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
    const RESULTS_CACHE_KEY = 'result_of_championship';
    const DRIVER_URL = 'https://api-formula-1.p.rapidapi.com/rankings/drivers?season=2025';
    const CONSTRUCT_URL = 'https://api-formula-1.p.rapidapi.com/rankings/teams?season=2025';
    const API_HOST = 'api-formula-1.p.rapidapi.com';

    /**
     * @Route("module/championship_result", name="championship_result", methods={"GET"})
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    public function indexAction(FileCache $cache)
    {
        $data = $cache->get(self::RESULTS_CACHE_KEY);

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
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getResults(FileCache $cache)
    {
        $client = new Client();

        $response = $client->request('GET', self::DRIVER_URL, [
            'headers' => [
                'x-rapidapi-host' => self::API_HOST,
                'x-rapidapi-key' => $_ENV['RAPIDAPI_KEY'],
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);

        $driverStandings = [];

        foreach ($responseData['response'] as $item) {
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

        $response = $client->request('GET', self::CONSTRUCT_URL, [
            'headers' => [
                'x-rapidapi-host' => self::API_HOST,
                'x-rapidapi-key' => $_ENV['RAPIDAPI_KEY'],
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);

        foreach ($responseData['response'] as $item) {
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
}
