<?php

namespace App\E2e;

use App\Cache\ResultCache;
use App\Cache\FileCache;
use App\Command\E2e\E2eGuard;
use App\Controller\Module\ResultsOfChampionshipController;
use App\Entity\ChampionshipPoint;
use App\Entity\Driver;
use App\Entity\Feed;
use App\Entity\Prize;
use App\Entity\PrizeDraw;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * E2E tesztek alapadatai: séma újraépítése, pilóták, felhasználók, bajnoki pontozási tábla, nyeremény, szabályok oldal,
 * pilóta-VB cache.
 * Csak az e2e környezetben használható (a hívó parancs ellenőrzi az E2eGuard-dal).
 */
class E2eFixtures
{
    /** bcrypt hash az "e2e-pass" jelszóhoz – fix, hogy a mentett session reset után is érvényes maradjon */
    public const PASSWORD_HASH = '$2y$13$xdVR42q.RyNdwdAiD0TzQO8qTEL3BmfbWrFuuB3tNGoHb/Ix/i6ii';

    /** kód => [név, pont (sorrend)] */
    public const DRIVERS = [
        'VER' => ['Max Verstappen', 14],
        'NOR' => ['Lando Norris', 13],
        'LEC' => ['Charles Leclerc', 12],
        'PIA' => ['Oscar Piastri', 11],
        'HAM' => ['Lewis Hamilton', 10],
        'RUS' => ['George Russell', 9],
        'SAI' => ['Carlos Sainz', 8],
        'ALO' => ['Fernando Alonso', 7],
        'GAS' => ['Pierre Gasly', 6],
        'ALB' => ['Alexander Albon', 5],
        'TSU' => ['Yuki Tsunoda', 4],
        'STR' => ['Lance Stroll', 3],
        'OCO' => ['Esteban Ocon', 2],
        'HUL' => ['Nico Hulkenberg', 1],
    ];

    /** username => [név, szerepkörök, alternatív bajnokság tagja] */
    public const USERS = [
        'e2e_user' => ['E2E User1', ['ROLE_USER'], true],
        'e2e_user2' => ['E2E User2', ['ROLE_USER'], true],
        'e2e_user3' => ['E2E User3', ['ROLE_USER'], true],
        'e2e_admin' => ['E2E Admin', ['ROLE_ADMIN'], false],
        // az EasyAdmin a pilóta / esemény / user / üzenet / információ / beállítás menüket ROLE_SUPER_ADMIN-hoz köti
        'e2e_superadmin' => ['E2E Superadmin', ['ROLE_SUPER_ADMIN'], false],
    ];

    public const RULES_INFORMATION_ID = 23;

    /** bajnoki pontozási tábla (helyezés => pont): az éles kezdő értékek (docs/sql/2026-11-23-championship-point.sql) */
    public const POINT_TABLE = [1 => 15, 2 => 11, 3 => 9, 4 => 7, 5 => 6, 6 => 5, 7 => 4, 8 => 2, 9 => 1, 10 => 0];

    /** nyeremény (helyezés => összeg) és megjegyzés, lezárt nevezéssel: az éles kezdő értékek (docs/sql/2026-11-23-prize-draw.sql) */
    public const PRIZES = [1 => 24000, 2 => 14400, 3 => 9600];
    public const PRIZE_NOTE = '12 résztvevő X 5000 = 60000 - 12000(üzemeltetés) = 48000';

    private EntityManagerInterface $em;
    private FileCache $fileCache;
    private string $kernelEnv;

    public function __construct(EntityManagerInterface $em, FileCache $fileCache, string $kernelEnv)
    {
        $this->em = $em;
        $this->fileCache = $fileCache;
        $this->kernelEnv = $kernelEnv;
    }

    /**
     * Séma eldobása és újraépítése, majd az alapadatok betöltése.
     */
    public function rebuild(): void
    {
        $this->assertSafe();

        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);
        $this->em->clear();

        $this->loadBase();
    }

    public function loadBase(): void
    {
        $this->assertSafe();

        foreach (self::DRIVERS as $code => [$name, $point]) {
            $driver = new Driver();
            $driver->setShort($code);
            $driver->setName($name);
            $driver->setPoint($point);
            $driver->setStatus(true);
            $this->em->persist($driver);
        }

        foreach (self::USERS as $username => [$name, $roles, $isAlterChamps]) {
            $user = new User();
            $user->setUsername($username);
            $user->setName($name);
            $user->setRoles($roles);
            $user->setPassword(self::PASSWORD_HASH);
            $user->setIsAlterChamps($isAlterChamps);
            $this->em->persist($user);
        }

        foreach (self::POINT_TABLE as $place => $points) {
            $this->em->persist((new ChampionshipPoint())->setPlace($place)->setPoints($points));
        }

        // az első és egyetlen sor: id = PrizeDraw::ID
        $prizeDraw = (new PrizeDraw())->setEntryOpen(false)->setNote(self::PRIZE_NOTE);
        foreach (self::PRIZES as $place => $amount) {
            $prizeDraw->addPrize((new Prize())->setPlace($place)->setAmount($amount));
        }
        $this->em->persist($prizeDraw);

        // hír a főoldal hírfolyamába (élesben a /cron/collect_feeds tölti az f1vilag.hu-ról)
        $feed = new Feed();
        $feed->setId(1); // a Feed id-ja nem generált (élesben a feed elemből jön)
        $feed->setTitle('E2E hír: lezárult a szezon előtti teszt');
        $feed->setDescription('<p>Az E2E csapat minden kört lefutott.</p>');
        $feed->setLink('http://127.0.0.1:8001/');
        $feed->setPublishDate(new \DateTime('-1 hour'));
        $feed->setImage('data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $this->em->persist($feed);

        $this->em->flush();

        // fix id kell: a Szabályok oldal az /api/information/23-at kéri
        $this->em->getConnection()->insert('information', [
            'id' => self::RULES_INFORMATION_ID,
            'title' => 'Szabályok',
            'content' => '<p>E2E szabályok: időmérő 4/1, futam 3/1, safety car 3 pont.</p>',
            'news' => 0,
            'slug' => 'szabalyok',
        ]);
    }

    /**
     * Minden cache ürítése (Doctrine result + second-level, FileCache), majd a pilóta-VB állás visszaírása,
     * hogy az oldalak ne hívják a rapidapi-t.
     */
    public function clearCaches(): void
    {
        $this->assertSafe();

        ResultCache::of($this->em)->deleteAll();

        $cache = $this->em->getCache();
        if ($cache) {
            $cache->evictEntityRegions();
            $cache->evictCollectionRegions();
            $cache->evictQueryRegions();
        }

        $this->fileCache->clearAll();
        $this->fileCache->save(ResultsOfChampionshipController::RESULTS_CACHE_KEY, self::championshipStandings());
    }

    /** e2e kernel-környezet ÉS _e2e végű adatbázis – különben kivétel, mielőtt bármit törölnénk */
    public function assertSafe(): void
    {
        E2eGuard::assertE2e($this->kernelEnv);
        E2eGuard::assertE2eDatabase($this->em->getConnection()->getParams()['dbname'] ?? null);
    }

    private static function championshipStandings(): array
    {
        $driver = fn (int $position, int $points, int $wins, string $given, string $family, string $code) => [
            'position' => $position,
            'points' => $points,
            'wins' => $wins,
            'Driver' => [
                'driverId' => $position,
                'givenName' => $given,
                'familyName' => $family,
                'code' => $code,
                'permanentNumber' => $position,
                'image' => '',
            ],
        ];

        return [
            'event' => 1,
            'driverStandings' => [
                $driver(1, 300, 6, 'Max', 'Verstappen', 'VER'),
                $driver(2, 280, 5, 'Lando', 'Norris', 'NOR'),
                $driver(3, 250, 3, 'Charles', 'Leclerc', 'LEC'),
            ],
            'constructStandings' => [
                ['position' => 1, 'points' => 500, 'Constructor' => ['name' => 'McLaren']],
                ['position' => 2, 'points' => 450, 'Constructor' => ['name' => 'Red Bull Racing']],
                ['position' => 3, 'points' => 400, 'Constructor' => ['name' => 'Ferrari']],
            ],
        ];
    }
}
