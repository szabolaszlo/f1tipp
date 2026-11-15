<?php

namespace App\Tests\Integration\Controller;

use App\Cache\FileCache;
use App\Controller\Module\ResultsOfChampionshipController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ChampionshipResultColorsTest extends WebTestCase
{
    /** @var array|null az éles/dev cache értéke, amit a teszt után visszaállítunk (közös var/cache/file-cache) */
    private $original;
    private $cache;

    public function test_standings_include_team_colors(): void
    {
        $client = static::createClient();
        $this->cache = static::getContainer()->get(FileCache::class);
        $this->original = $this->cache->get(ResultsOfChampionshipController::RESULTS_CACHE_KEY);

        $this->cache->save(ResultsOfChampionshipController::RESULTS_CACHE_KEY, [
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
    }

    protected function tearDown(): void
    {
        if ($this->cache !== null) {
            if (!empty($this->original)) {
                $this->cache->save(ResultsOfChampionshipController::RESULTS_CACHE_KEY, $this->original);
            } else {
                (new FilesystemAdapter('', 0, static::getContainer()->getParameter('kernel.project_dir') . '/var/cache/file-cache'))
                    ->deleteItem(ResultsOfChampionshipController::RESULTS_CACHE_KEY);
            }
        }
        parent::tearDown();
    }
}
