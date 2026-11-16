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

    /** szezon szenárió: 4 hétvége, az első háromnak már van eredménye (időmérő Q, futam Q + SC Igen) */
    public const SEASON_NAMES = ['E2E 1. Nagydíj', 'E2E 2. Nagydíj', 'E2E 3. Nagydíj', 'E2E 4. Nagydíj'];

    /** hétvége (0-tól) => username => [időmérő tipp, futam tipp, safety car] */
    public const SEASON_BETS = [
        [
            'e2e_user' => [self::Q, self::Q, 'Nem'],
            'e2e_user2' => [self::Q_SWAPPED, self::Q, 'Igen'],
            'e2e_user3' => [self::Q_OUTSIDERS, self::Q_OUTSIDERS, 'Igen'],
        ],
        [
            'e2e_user' => [self::Q_SWAPPED, self::Q, 'Nem'],
            'e2e_user2' => [self::Q, self::Q, 'Igen'],
            'e2e_user3' => [self::Q_OUTSIDERS, self::Q_OUTSIDERS, 'Igen'],
        ],
        [
            'e2e_user' => [self::Q_SWAPPED, self::Q, 'Nem'],
            'e2e_user2' => [self::Q_OUTSIDERS, self::Q_OUTSIDERS, 'Nem'],
            'e2e_user3' => [self::Q, self::Q, 'Igen'],
        ],
    ];

    /** akik a "mások tippjei" szenáriókban már leadták az időmérő tippjüket (e2e_user még nem) */
    public const OTHERS_QUALIFY_BETS = [
        'e2e_user2' => self::Q_SWAPPED,
        'e2e_user3' => self::Q_OUTSIDERS,
    ];

    public const NAMES = [
        'open-weekend', 'deadline-passed', 'about-to-close', 'bets-placed', 'results-entered',
        'others-bet', 'others-bet-closed', 'race-closer', 'recent-weekend', 'finished-weekend',
        'sprint-bets-placed', 'season', 'results-without-user3',
    ];

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

        if ($name === 'season') {
            return $this->buildSeason($now);
        }

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
            case 'others-bet':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('+2 hours')),
                    'race' => $this->event(new Race(), 1, $now->modify('+1 day')),
                ];
                break;
            case 'others-bet-closed':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-1 hour')),
                    'race' => $this->event(new Race(), 1, $now->modify('+1 day')),
                ];
                break;
            case 'race-closer':
                // vasárnap délelőtt: a szombati időmérő 20 órája volt, a futam 2 óra múlva
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-20 hours')),
                    'race' => $this->event(new Race(), 1, $now->modify('+2 hours')),
                ];
                break;
            case 'recent-weekend':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-2 days -1 hour')),
                    'race' => $this->event(new Race(), 1, $now->modify('-2 days')),
                ];
                break;
            case 'finished-weekend':
                $events = [
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-4 days -1 hour')),
                    'race' => $this->event(new Race(), 1, $now->modify('-4 days')),
                ];
                break;
            case 'sprint-bets-placed':
                $events = [
                    'sprint_qualify' => $this->event(new SprintQualify(), 1, $now->modify('-3 hours')),
                    'qualify' => $this->event(new Qualify(), 1, $now->modify('-2 hours')),
                    'race' => $this->event(new Race(), 1, $now->modify('-1 hour')),
                ];
                break;
            default: // bets-placed, results-entered, results-without-user3
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
            'events' => $this->eventInfo($events),
        ];

        if (in_array($name, ['bets-placed', 'results-entered', 'recent-weekend', 'finished-weekend'], true)) {
            $this->placeBets($events['qualify'], $events['race']);
        }

        if ($name === 'sprint-bets-placed') {
            $this->placeBets($events['qualify'], $events['race']);
            foreach (self::BETS as $username => [$qualifyTip]) {
                $this->bet($this->user($username), $events['sprint_qualify'], array_slice($qualifyTip, 0, 8));
            }
            $this->em->flush();
        }

        if ($name === 'others-bet' || $name === 'others-bet-closed') {
            foreach (self::OTHERS_QUALIFY_BETS as $username => $tip) {
                $this->bet($this->user($username), $events['qualify'], $tip);
            }
            $this->em->flush();
        }

        if ($name === 'results-without-user3') {
            // e2e_user3 nem tippelt: a hétvégén 0 pontos
            $this->placeBets($events['qualify'], $events['race'], ['e2e_user', 'e2e_user2']);
        }

        if (in_array($name, ['results-entered', 'recent-weekend', 'finished-weekend', 'results-without-user3'], true)) {
            $info['results'] = [
                'qualify' => $this->result($events['qualify'], self::Q)->getId(),
                'race' => $this->result($events['race'], self::Q, self::RACE_SAFETY_CAR)->getId(),
            ];
            $this->calculator->calculate();
        }

        return $info;
    }

    /**
     * Szezon közben: 3 lezajlott hétvége (3, 2 és 1 hete) eredménnyel, a 4. jövő héten.
     * Az eredményeket hétvégénként, időrendben számoljuk, ahogy élesben az admin rögzítené.
     */
    private function buildSeason(\DateTimeImmutable $now): array
    {
        $events = [];
        foreach (self::SEASON_NAMES as $i => $weekendName) {
            $weekend = $i + 1;
            // 21, 14 és 7 napja, a 4. hétvége 7 nap múlva
            $daysAgo = $weekend <= 3 ? (4 - $weekend) * 7 : -7;
            $raceAt = $now->modify(sprintf('%+d days', -$daysAgo));
            $events['w' . $weekend . '_qualify'] = $this->event(new Qualify(), $weekend, $raceAt->modify('-1 day'), $weekendName);
            $events['w' . $weekend . '_race'] = $this->event(new Race(), $weekend, $raceAt, $weekendName);
        }
        $this->em->flush();

        $info = ['serverNow' => $now->getTimestamp(), 'events' => $this->eventInfo($events), 'results' => []];

        foreach (self::SEASON_BETS as $i => $bets) {
            $w = 'w' . ($i + 1);
            foreach ($bets as $username => [$qualifyTip, $raceTip, $safetyCar]) {
                $user = $this->user($username);
                $this->bet($user, $events[$w . '_qualify'], $qualifyTip);
                $this->bet($user, $events[$w . '_race'], $raceTip, $safetyCar);
            }
            $this->em->flush();

            $info['results'][$w . '_qualify'] = $this->result($events[$w . '_qualify'], self::Q)->getId();
            $this->calculator->calculate();
            $info['results'][$w . '_race'] = $this->result($events[$w . '_race'], self::Q, self::RACE_SAFETY_CAR)->getId();
            $this->calculator->calculate();
        }

        return $info;
    }

    /** @param array<string, Event> $events */
    private function eventInfo(array $events): array
    {
        return array_map(fn (Event $event) => [
            'id' => $event->getId(),
            'deadline' => $event->getDateTime()->getTimestamp(),
        ], $events);
    }

    private function user(string $username): User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
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

    private function placeBets(Event $qualify, Event $race, ?array $onlyUsers = null): void
    {
        foreach (self::BETS as $username => [$qualifyTip, $raceTip, $safetyCar]) {
            if ($onlyUsers !== null && !in_array($username, $onlyUsers, true)) {
                continue;
            }
            $user = $this->user($username);
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
