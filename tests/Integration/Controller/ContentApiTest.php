<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Information;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * A Vue komponensek (Calendar, News, Rules) által olvasott, csak olvasó JSON végpontok.
 * A válaszformátum az API Platform 2.7 korábbi kimenetével egyezik (sima JSON tömb/objektum, nincs hydra).
 */
class ContentApiTest extends WebTestCase
{
    public function test_events_are_listed_as_plain_json_array(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/events', [], [], ['HTTP_ACCEPT' => 'application/json, text/plain, */*']);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertTrue(array_is_list($data));
        self::assertLessThanOrEqual(100, count($data));

        $previousId = 0;
        foreach ($data as $event) {
            self::assertSame(['id', 'weekendOrder', 'name', 'date_time', 'dateTime', 'type'], array_keys($event));
            self::assertContains($event['type'], ['race', 'qualify', 'sprint_qualify', 'event']);
            self::assertSame($event['date_time'], $event['dateTime']);
            self::assertNotFalse(\DateTimeImmutable::createFromFormat(\DATE_RFC3339, $event['dateTime']));
            self::assertGreaterThan($previousId, $event['id']);
            $previousId = $event['id'];
        }
    }

    public function test_feeds_are_listed_newest_first_ten_per_page(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/feeds');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertTrue(array_is_list($data));
        self::assertLessThanOrEqual(10, count($data));

        $previousId = PHP_INT_MAX;
        foreach ($data as $feed) {
            self::assertSame(['id', 'title', 'description', 'link', 'publishDate', 'image'], array_keys($feed));
            self::assertLessThan($previousId, $feed['id']);
            $previousId = $feed['id'];
        }
    }

    public function test_information_item_is_returned_by_id(): void
    {
        $client = static::createClient();
        /** @var Information|null $information */
        $information = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Information::class)->findOneBy([]);
        if ($information === null) {
            self::markTestSkipped('Nincs information rekord az adatbázisban.');
        }

        $client->request('GET', '/api/information/' . $information->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');
        self::assertSame([
            'id' => $information->getId(),
            'title' => $information->getTitle(),
            'content' => $information->getContent(),
            'news' => $information->getNews(),
            'slug' => $information->getSlug(),
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function test_unknown_information_is_404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/information/999999999');

        self::assertResponseStatusCodeSame(404);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
