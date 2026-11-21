<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 25.
 * Time: 11:06
 */

namespace App\Controller\Module;

use App\Cache\FileCache;
use App\Display\TeamColorProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ResultsOfChampionshipController
 * @package App\Controller\Module\ResultsOfChampionshipController
 */
class ResultsOfChampionshipController extends AbstractController
{
    const RESULTS_CACHE_KEY = 'result_of_championship';
    const DRIVER_URL = 'https://api-formula-1.p.rapidapi.com/rankings/drivers?season=%d';
    const CONSTRUCT_URL = 'https://api-formula-1.p.rapidapi.com/rankings/teams?season=%d';
    const API_HOST = 'api-formula-1.p.rapidapi.com';

    /**
     * @param FileCache $cache
     * @param TeamColorProvider $teamColors
     * @return Response
     * @throws InvalidArgumentException
     */
    #[Route(path: 'module/championship_result', name: 'championship_result', methods: ['GET'])]
    public function indexAction(FileCache $cache, TeamColorProvider $teamColors)
    {
        $data = $cache->get(self::RESULTS_CACHE_KEY);

        if (empty($data)) {
            $data = $this->getResults($cache);
        }

        if ($data === null) {
            return new JsonResponse(['error' => 'championship_result_unavailable'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        foreach ($data['driverStandings'] ?? [] as $i => $standing) {
            $data['driverStandings'][$i]['teamColor'] = $teamColors->colorForDriver((string)($standing['Driver']['code'] ?? ''));
        }

        foreach ($data['constructStandings'] ?? [] as $i => $standing) {
            $data['constructStandings'][$i]['teamColor'] = $teamColors->colorForTeam((string)($standing['Constructor']['name'] ?? ''));
        }

        $data['id'] = 'resultsOfChampionship';

        return new JsonResponse($data);
    }

    /**
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    #[Route(path: 'cron/championship_result_cache_warmer', name: 'championship_result_cache_warmer', methods: ['GET'])]
    public function getResultsAction(FileCache $cache): Response
    {
        if ($this->getResults($cache) === null) {
            return new Response('RapidAPI error', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new Response('OK', 200);
    }

    /**
     * @param FileCache $cache
     * @return array|null null, ha az API hibát adott (ilyenkor a cache marad)
     * @throws InvalidArgumentException
     */
    protected function getResults(FileCache $cache)
    {
        // timeout nélkül egy lassú/lógó RapidAPI a PHP-FPM workert is megfogná
        $client = new Client(['timeout' => 5, 'connect_timeout' => 3]);

        // hibás / kvótán túli API-válasznál nem írjuk felül a cache-t üres tabellával (azt a következő
        // cron-futásig szolgálnánk ki), a hívó pedig 503-at ad 500 helyett
        // az aktuális szezon; az első futam előtt (üres tabella) még a tavalyit mutatjuk
        $season = (int) date('Y');
        $drivers = $this->fetchRanking($client, sprintf(self::DRIVER_URL, $season));

        if ($drivers === []) {
            $season--;
            $drivers = $this->fetchRanking($client, sprintf(self::DRIVER_URL, $season));
        }

        $teams = $this->fetchRanking($client, sprintf(self::CONSTRUCT_URL, $season));

        if ($drivers === null || $teams === null) {
            return null;
        }

        $driverStandings = [];

        foreach ($drivers as $item) {
            $driverStandings[] = [
                'position' => $item['position'],
                'points' => $item['points'],
                'wins' => $item['wins'],
                'Driver' => [
                    'driverId' => $item['driver']['id'],
                    'givenName' => explode(' ', $item['driver']['name'])[0],
                    'familyName' => implode(' ', array_slice(explode(' ', $item['driver']['name']), 1)),
                    'code' => $item['driver']['abbr'],
                    'permanentNumber' => $item['driver']['number'],
                    'image' => $item['driver']['image'],
                ]
            ];
        }

        $constructStandings = [];

        foreach ($teams as $item) {
            $constructStandings[] = [
                'position' => $item['position'],
                'points' => $item['points'],
                'Constructor' => [
                    'name' => $item['team']['name'],
                ]
            ];
        }

        $data = [
            'event' => 1,
            'driverStandings' => $driverStandings,
            'constructStandings' => $constructStandings,
        ];

        $cache->save(self::RESULTS_CACHE_KEY, $data);

        return $data;
    }

    /**
     * @return array|null a "response" lista, vagy null, ha az API hibát adott
     */
    private function fetchRanking(Client $client, string $url): ?array
    {
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'x-rapidapi-host' => self::API_HOST,
                    'x-rapidapi-key' => $_ENV['RAPIDAPI_KEY'] ?? '',
                ],
            ]);
        } catch (GuzzleException) {
            return null;
        }

        $responseData = json_decode((string) $response->getBody(), true);

        return is_array($responseData['response'] ?? null) ? $responseData['response'] : null;
    }
}
