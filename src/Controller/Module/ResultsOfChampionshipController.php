<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 25.
 * Time: 11:06
 */

namespace App\Controller\Module;

use App\Cache\FileCache;
use GuzzleHttp\Client;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResultsOfChampionshipController
 * @package App\Controller\Module\ResultsOfChampionshipController
 */
class ResultsOfChampionshipController extends AbstractController
{
    private const RESULTS_CACHE_KEY_PREFIX = 'result_of_championship_';
    private const DRIVER_URL = 'https://api-formula-1.p.rapidapi.com/rankings/drivers?season=';
    private const CONSTRUCT_URL = 'https://api-formula-1.p.rapidapi.com/rankings/teams?season=';
    private const API_HOST = 'api-formula-1.p.rapidapi.com';

    /**
     * @Route("module/championship_result", name="championship_result", methods={"GET"})
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    public function indexAction(FileCache $cache): Response
    {
        $cacheKey = $this->getCacheKey();
        $data = $cache->get($cacheKey);

        if (empty($data)) {
            $data = $this->getResults($cache);
        }

        $data['id'] = 'resultsOfChampionship';

        return new JsonResponse($data);
    }

    /**
     * @Route("cron/championship_result_cache_warmer", name="championship_result_cache_warmer", methods={"GET"})
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    public function getResultsAction(FileCache $cache): Response
    {
        $this->getResults($cache);

        return new Response('OK', 200);
    }

    /**
     * @param FileCache $cache
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getResults(FileCache $cache): array
    {
        $client = new Client();
        $season = $this->getSeason();

        $driverResponseData = $this->requestApi($client, self::DRIVER_URL . $season);
        $constructResponseData = $this->requestApi($client, self::CONSTRUCT_URL . $season);

        $driverStandings = [];
        foreach ($driverResponseData['response'] ?? [] as $item) {
            $driverNameParts = explode(' ', $item['driver']['name']);

            $driverStandings[] = [
                'position' => $item['position'],
                'points' => $item['points'],
                'wins' => $item['wins'],
                'Driver' => [
                    'driverId' => $item['driver']['id'],
                    'givenName' => $driverNameParts[0] ?? '',
                    'familyName' => implode(' ', array_slice($driverNameParts, 1)),
                    'code' => $item['driver']['abbr'],
                    'permanentNumber' => $item['driver']['number'],
                    'image' => $item['driver']['image'],
                ],
            ];
        }

        $constructStandings = [];
        foreach ($constructResponseData['response'] ?? [] as $item) {
            $constructStandings[] = [
                'position' => $item['position'],
                'points' => $item['points'],
                'Constructor' => [
                    'name' => $item['team']['name'],
                ],
            ];
        }

        $data = [
            'event' => 1,
            'driverStandings' => $driverStandings,
            'constructStandings' => $constructStandings,
        ];

        $cache->save($this->getCacheKey(), $data);

        return $data;
    }

    private function requestApi(Client $client, string $url): array
    {
        $response = $client->request('GET', $url, [
            'headers' => [
                'x-rapidapi-host' => self::API_HOST,
                'x-rapidapi-key' => $_ENV['RAPIDAPI_KEY'],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    private function getSeason(): int
    {
        return (int) date('Y');
    }

    private function getCacheKey(): string
    {
        return self::RESULTS_CACHE_KEY_PREFIX . $this->getSeason();
    }
}
