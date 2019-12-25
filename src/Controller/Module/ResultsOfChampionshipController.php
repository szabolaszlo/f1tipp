<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 25.
 * Time: 11:06
 */

namespace App\Controller\Module;

use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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

    /**
     * @return Response
     * @throws InvalidArgumentException
     */
    public function indexAction()
    {
        $cache = new FilesystemAdapter();

        $cachedItem = $cache->getItem('result_of_championship');

        if (!$cachedItem->isHit()) {
            $this->getResults();
            $cachedItem = $cache->getItem('result_of_championship');
        }

        $data = (array)$cachedItem->get();

        $data['id'] = 'resultsOfChampionship';

        return $this->render('controller/module/results_of_championship/results_of_championship.html.twig', $data);
    }

    /**
     * @Route("championship_result_cache_warmer", name="championship_result_cache_warmer", methods={"GET"})
     * @return Response
     * @throws InvalidArgumentException
     */
    public function getResultsAction()
    {
        $this->getResults();
        return new Response('OK', 200);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getResults()
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

        $cache = new FilesystemAdapter();
        $result = $cache->getItem('result_of_championship');
        $result->set($data);
        $cache->save($result);
    }
}
