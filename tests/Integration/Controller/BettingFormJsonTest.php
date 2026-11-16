<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * A tippelő form JSON végpontja. A lokális adatbázison fut, de csak olvas:
 * a POST-teszt szándékosan érvénytelen (duplikált pilóta), így nem ment tippet.
 */
class BettingFormJsonTest extends WebTestCase
{
    public function test_requires_login(): void
    {
        $client = static::createClient();
        // a teszt környezetben a hibaoldal sablonja nem renderelhető (ga4_measurement_id), ezért a kivételt nézzük
        $client->catchExceptions(false);

        $this->expectException(UnauthorizedHttpException::class);
        $client->request('GET', '/module/betting/form/1');
    }

    public function test_form_is_described_as_json_with_tv_style_driver_data(): void
    {
        [$client, $event] = $this->loggedInClientWithEvent();

        $client->request('GET', '/module/betting/form/' . $event->getId());
        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertSame($event->getId(), $data['eventId']);
        self::assertSame('/module/betting/form/' . $event->getId(), $data['action']);
        self::assertContains('betting_form[_token]', array_column($data['hidden'], 'name'));
        self::assertContains('betting_form[attributes][0][key]', array_column($data['hidden'], 'name'));

        if ($data['fields'] === []) {
            // lejárt határidő tipp nélkül: nincs mező, csak hibaüzenet
            self::assertNotEmpty($data['errors']);
            return;
        }

        $first = $data['fields'][0];
        self::assertSame('betting_form[attributes][0][value]', $first['name']);
        self::assertSame('p1', $first['key']);
        self::assertSame('driver', $first['type']);
        self::assertStringNotContainsString(':', $first['label']);

        self::assertNotEmpty($data['drivers']);
        foreach ($data['drivers'] as $driver) {
            self::assertMatchesRegularExpression('/^[A-Z]{3}$/', $driver['code']);
            self::assertNotSame('', $driver['familyName']);
            self::assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $driver['teamColor']);
        }
        self::assertStringNotContainsString('[', $data['drivers'][0]['familyName']);
    }

    public function test_duplicate_drivers_are_rejected_with_errors(): void
    {
        [$client, $event] = $this->loggedInClientWithEvent();

        $client->request('GET', '/module/betting/form/' . $event->getId());
        $data = json_decode($client->getResponse()->getContent(), true);

        $driverFields = array_values(array_filter($data['fields'], fn ($f) => $f['type'] === 'driver'));
        if (count($driverFields) < 2 || $data['drivers'] === []) {
            self::markTestSkipped('Nincs tippelhető esemény a lokális adatbázisban.');
        }

        $payload = [];
        foreach ($data['hidden'] as $hidden) {
            $payload[$hidden['name']] = $hidden['value'];
        }
        foreach ($data['fields'] as $field) {
            $payload[$field['name']] = $field['type'] === 'driver'
                ? $data['drivers'][0]['code']
                : ($data['questionChoices'][0]['value'] ?? '');
        }

        $client->request('POST', $data['action'], $this->nest($payload));

        self::assertSame(422, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertNotEmpty($response['errors']);
        self::assertNull($response['success']);
    }

    /**
     * @return array{0: KernelBrowser, 1: Event}
     */
    private function loggedInClientWithEvent(): array
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy([]);
        $event = $em->getRepository(Event::class)->getNextEvent();
        if (!$user || !$event) {
            self::markTestSkipped('Nincs felhasználó vagy következő esemény a lokális adatbázisban.');
        }

        $client->loginUser($user);

        return [$client, $event];
    }

    /**
     * "betting_form[attributes][0][value]" => x  ->  ['betting_form' => ['attributes' => [0 => ['value' => x]]]]
     */
    private function nest(array $flat): array
    {
        parse_str(http_build_query($flat), $nested);

        return $nested;
    }
}
