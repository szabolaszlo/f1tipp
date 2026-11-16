<?php

namespace App\Tests\Integration\Controller;

use App\Cache\FileCache;
use App\Controller\Module\ResultsOfChampionshipController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

class ChampionshipResultColorsTest extends WebTestCase
{
    /** @var string ideiglenes könyvtár: a FileCache itt dolgozik, nem a dev/éles közös var/cache/file-cache-ben */
    private $tmpDir;

    public function test_standings_include_team_colors(): void
    {
        $client = static::createClient();
        $sharedBefore = $this->sharedCacheHit();

        $this->tmpDir = sys_get_temp_dir() . '/f1tipp-test-' . uniqid();
        $cache = new FileCache(new ParameterBag(['app.file_cache_dir' => $this->tmpDir . '/var/cache/file-cache']));
        static::getContainer()->set(FileCache::class, $cache);

        $cache->save(ResultsOfChampionshipController::RESULTS_CACHE_KEY, [
            'event' => 1,
            'driverStandings' => [[
                'position' => 1, 'points' => 421, 'wins' => 8,
                'Driver' => ['driverId' => 1, 'givenName' => 'Max', 'familyName' => 'Verstappen', 'code' => 'VER', 'permanentNumber' => 1, 'image' => ''],
            ], [
                'position' => 2, 'points' => 10, 'wins' => 0,
                'Driver' => ['driverId' => 2, 'givenName' => 'Jack', 'familyName' => 'Doohan', 'code' => 'DOO', 'permanentNumber' => 7, 'image' => ''],
            ]],
            'constructStandings' => [[
                'position' => 1, 'points' => 833, 'Constructor' => ['name' => 'McLaren Racing'],
            ]],
        ]);

        $client->request('GET', '/module/championship_result');
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertSame('#3671C6', $data['driverStandings'][0]['teamColor']);
        self::assertSame('#6b6b78', $data['driverStandings'][1]['teamColor']);
        self::assertSame('#FF8000', $data['constructStandings'][0]['teamColor']);
        self::assertSame($sharedBefore, $this->sharedCacheHit(), 'a teszt nem írhat a dev/éles közös var/cache/file-cache könyvtárba');
    }

    private function sharedCacheHit(): bool
    {
        return (new FilesystemAdapter('', 0, static::getContainer()->getParameter('kernel.project_dir') . '/var/cache/file-cache'))
            ->getItem(ResultsOfChampionshipController::RESULTS_CACHE_KEY)->isHit();
    }

    protected function tearDown(): void
    {
        if ($this->tmpDir !== null) {
            (new Filesystem())->remove($this->tmpDir);
        }
        parent::tearDown();
    }
}
