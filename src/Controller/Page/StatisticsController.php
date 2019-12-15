<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 04. 01.
 * Time: 12:54
 */

namespace App\Controller\Page;

use App\LegacyService\Statistics\ObjectSorter;
use App\LegacyService\Statistics\StatisticsCalculator;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StatisticsController
 * @package App\Controller\Page\StatisticsController
 */
class StatisticsController extends AbstractController
{
    /**
     * @var ObjectSorter
     */
    protected $sorter;

    /**
     * @var StatisticsCalculator
     */
    protected $calculator;

    /**
     * @Route("/statistics", name="statistics", methods={"GET"})
     * @param ObjectSorter $sorter
     * @param StatisticsCalculator $calculator
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function indexAction(ObjectSorter $sorter, StatisticsCalculator $calculator)
    {
        $this->sorter = $sorter;
        $this->calculator = $calculator;

        $bets = $this->getDoctrine()->getRepository('App:Bet')->findAll();
        $results = $this->getDoctrine()->getRepository('App:Result')->findAll();

        $cacheKey = 'statistics' . count($results);

        $cache = new FilesystemAdapter();

        $cachedItem = $cache->getItem($cacheKey);

        if (!$cachedItem->isHit()) {
            $data = (array)$cachedItem->get();

            $data['statistics']['bets'] = $this->getStatistics($bets);
            $data['statistics']['results'] = $this->getStatistics($results);

            $cachedItem->set($data);
            $cache->save($cachedItem);
        }

        return $this->render('controller/page/statistics.html.twig', $cachedItem->get());
    }

    /**
     * @param array $objects
     * @return array
     * @throws Exception
     */
    protected function getStatistics($objects = array())
    {
        $statistics = array();

        $this->sorter->addObjects($objects);

        $qualify = $this->sorter->getObjectsByType('qualify');
        $race = $this->sorter->getObjectsByType('race');

        $statistics['qualify'] = $this->calculator->getStatistics($qualify);
        $statistics['race'] = $this->calculator->getStatistics($race);

        return $statistics;
    }
}
