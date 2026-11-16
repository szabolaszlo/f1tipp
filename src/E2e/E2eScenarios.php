<?php

namespace App\E2e;

use App\Calculator\Calculator;
use App\Command\E2e\E2eGuard;
use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Event;
use App\Entity\Qualify;
use App\Entity\Race;
use App\Entity\Result;
use App\Entity\ResultAttribute;
use App\Entity\SprintQualify;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * E2E szenárió-állapotok. Minden időpont a futás pillanatához relatív, mert a tippelési
 * határidőt a szerver a saját órájához méri. Az eseményeket időrendben, az 1. hétvégével kezdve
 * kell beszúrni: az EventRepository::getNextEvent() ORDER BY nélkül, id-sorrendben választ.
 */
class E2eScenarios
{
    public const WEEKEND_NAME = 'E2E Nagydíj';
    public const NEXT_WEEKEND_NAME = 'E2E Második Nagydíj';

    /** a "Q" sorrend: az eredmény, és e2e_user időmérő tippje */
    public const Q = ['VER', 'NOR', 'LEC', 'PIA', 'HAM', 'RUS', 'SAI', 'ALO', 'GAS', 'ALB'];
    public const Q_SWAPPED = ['NOR', 'VER', 'LEC', 'PIA', 'HAM', 'RUS', 'SAI', 'ALO', 'GAS', 'ALB'];
    public const Q_OUTSIDERS = ['TSU', 'STR', 'OCO', 'HUL', 'VER', 'NOR', 'LEC', 'PIA', 'HAM', 'RUS'];

    /** username => [időmérő tipp, futam tipp, safety car] */
    public const BETS = [
        'e2e_user' => [self::Q, self::Q, 'Nem'],
        'e2e_user2' => [self::Q_SWAPPED, self::Q, 'Igen'],
        'e2e_user3' => [self::Q_OUTSIDERS, self::Q_OUTSIDERS, 'Igen'],
    ];

    public const RACE_SAFETY_CAR = 'Igen';

    public const NAMES = ['open-weekend', 'deadline-passed', 'about-to-close', 'bets-placed', 'results-entered'];

    private EntityManagerInterface $em;
    private Calculator $calculator;
    private string $kernelEnv;

    public function __construct(EntityManagerInterface $em, Calculator $calculator, string $kernelEnv)
    {
        $this->em = $em;
        $this->calculator = $calculator;
        $this->kernelEnv = $kernelEnv;
    }

    /**
     * Felépíti a szenáriót egy frissen resetelt adatbázison.
     *
     * @return array{serverNow: int, events: array<string, array{id: int, deadline: int}>, results?: array<string, int>}
     */
    public function build(string $name, int $deadlineIn): array
    {
        E2eGuard::assertE2e($this->kernelEnv);
        E2eGuard::assertE2eDatabase($this->em->getConnection()->getParams()['dbname'] ?? null);

        if (!in_array($name, self::NAMES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown scenario "%s". Valid: %s',
                $name,
                implode(', ', self::NAMES)
            ));
        }

        $now = new \DateTimeImmutable();

        switch ($name) {
            case 'open-weekend':
                $events = [
                    'sprint_qualify' => $this->event(new SprintQualify(), 1, $now->modify('+1 hour')),
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('+2 hours')),
                    'race' => $this->event(new Race(), 1, $now->modify('+1 day')),
                ];
                break;
            case 'deadline-passed':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-1 hour')),
                    'race' => $this->event(new Race(), 1, $now->modify('+1 day')),
                ];
                break;
            case 'about-to-close':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify(sprintf('+%d seconds', $deadlineIn))),
                    'race' => $this->event(new Race(), 1, $now->modify('+1 day')),
                ];
                break;
            default: // bets-placed, results-entered
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-2 hours')),
                    'race' => $this->event(new Race(), 1, $now->modify('-1 hour')),
                ];
        }

        // 2. hétvége: a valóságoshoz hasonló naptár; az 1. hétvége után kerül be (id-sorrend!)
        $this->event(new Qualify(), 2, $now->modify('+7 days'), self::NEXT_WEEKEND_NAME);
        $this->event(new Race(), 2, $now->modify('+8 days'), self::NEXT_WEEKEND_NAME);
        $this->em->flush();

        $info = [
            'serverNow' => $now->getTimestamp(),
            'events' => array_map(fn (Event $event) => [
                'id' => $event->getId(),
                'deadline' => $event->getDateTime()->getTimestamp(),
            ], $events),
        ];

        if ($name === 'bets-placed' || $name === 'results-entered') {
            $this->placeBets($events['qualify'], $events['race']);
        }

        if ($name === 'results-entered') {
            $info['results'] = [
                'qualify' => $this->result($events['qualify'], self::Q)->getId(),
                'race' => $this->result($events['race'], self::Q, self::RACE_SAFETY_CAR)->getId(),
            ];
            $this->calculator->calculate();
        }

        return $info;
    }

    private function event(Event $event, int $weekendOrder, \DateTimeImmutable $at, string $name = self::WEEKEND_NAME): Event
    {
        $event->setName($name);
        $event->setWeekendOrder($weekendOrder);
        $event->setDateTime(\DateTime::createFromImmutable($at));
        $this->em->persist($event);
        // azonnali flush: a Doctrine osztályonként csoportosítva szúrna be, nekünk időrendi id-k kellenek
        $this->em->flush();

        return $event;
    }

    private function placeBets(Event $qualify, Event $race): void
    {
        foreach (self::BETS as $username => [$qualifyTip, $raceTip, $safetyCar]) {
            $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
            $this->bet($user, $qualify, $qualifyTip);
            $this->bet($user, $race, $raceTip, $safetyCar);
        }
        $this->em->flush();
    }

    private function bet(User $user, Event $event, array $drivers, ?string $safetyCar = null): void
    {
        $bet = new Bet();
        $bet->setUser($user);
        $bet->setEvent($event);
        foreach ($this->attributeValues($drivers, $safetyCar) as $key => $value) {
            $attribute = new BetAttribute();
            $attribute->setKey($key);
            $attribute->setValue($value);
            $bet->addAttribute($attribute);
        }
        $this->em->persist($bet);
    }

    private function result(Event $event, array $drivers, ?string $safetyCar = null): Result
    {
        $result = new Result();
        $result->setEvent($event);
        $result->setIsCalculated(false);
        foreach ($this->attributeValues($drivers, $safetyCar) as $key => $value) {
            $attribute = new ResultAttribute();
            $attribute->setKey($key);
            $attribute->setValue($value);
            $result->addAttribute($attribute);
        }
        $this->em->persist($result);
        $this->em->flush();

        return $result;
    }

    /** @return array<string, string> p1..pN => kód, és opcionálisan sc => Igen/Nem */
    private function attributeValues(array $drivers, ?string $safetyCar): array
    {
        $values = [];
        foreach (array_values($drivers) as $i => $code) {
            $values['p' . ($i + 1)] = $code;
        }
        if ($safetyCar !== null) {
            $values['sc'] = $safetyCar;
        }

        return $values;
    }
}
