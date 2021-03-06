<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 04. 01.
 * Time: 12:54
 */

namespace Controller\Page\Statistics;

use Controller\Controller;

/**
 * Class Statistics
 * @package Controller\Page\Statistics
 */
class Statistics extends Controller
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
     * @return mixed
     */
    public function indexAction()
    {
        $cachedContent = $this->registry->getCache()->getCache($this->getCacheId());

        if ($cachedContent) {
            return $cachedContent;
        }

        $this->sorter = new ObjectSorter();
        $this->calculator = new StatisticsCalculator();

        $bets = $this->entityManager->getRepository('Entity\Bet')->findAll();
        $results = $this->entityManager->getRepository('Entity\Result')->findAll();

        $this->data['statistics']['bets'] = $this->getStatistics($bets);
        $this->data['statistics']['results'] = $this->getStatistics($results);

        $renderedContent = $this->render();

        $this->registry->getCache()->setCache($this->getCacheId(), $renderedContent);

        return $renderedContent;
    }

    /**
     * @param array $objects
     * @return array
     * @throws \Exception
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

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return 'statistics.' . count($this->entityManager->getRepository('Entity\Result')->findAll());
    }
}
