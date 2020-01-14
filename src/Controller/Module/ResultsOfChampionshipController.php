<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 25.
 * Time: 11:06
 */

namespace App\Controller\Module;

use App\Cache\FileCache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResultsOfChampionshipController
 * @package App\Controller\Module\ResultsOfChampionshipController
 */
class ResultsOfChampionshipController extends AbstractController
{
    const DRIVER_JSON_PATH = "http://ergast.com/api/f1/current/driverStandings.json";

    const CONSTRUCT_JSON_PATH = "http://ergast.com/api/f1/current/constructorStandings.json";

    const RESULTS_CACHE_KEY = 'result_of_championship';

    /**
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

        return $this->render('controller/module/results_of_championship/results_of_championship.html.twig', $data);
    }

    /**
     * @Route("championship_result_cache_warmer", name="championship_result_cache_warmer", methods={"GET"})
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    public function getResultsAction(FileCache $cache)
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
        $driverResponse = json_decode(file_get_contents(self::DRIVER_JSON_PATH), true);

        $constructResponse = json_decode(file_get_contents(self::CONSTRUCT_JSON_PATH), true);

        $weekendOrder = $driverResponse['MRData']['StandingsTable']['StandingsLists'][0]['round'];

        $event = $this->getDoctrine()
            ->getRepository('App\Entity\Race')
            ->findOneBy(array('weekendOrder' => $weekendOrder));

        $data['event'] = $event;

        $data['driverStandings'] =
            $driverResponse['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];

        $data['constructStandings'] =
            $constructResponse['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'];

        $cache->save(self::RESULTS_CACHE_KEY, $data);

        return $data;
    }
}
