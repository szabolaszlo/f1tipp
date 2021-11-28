<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */

namespace App\Controller\Page;

use App\Cache\FileCache;
use App\LegacyService\ResultTable\ResultTable;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Results
 * @package App\Controller\Page\Results
 */
class ResultsController extends AbstractController
{
    /**
     * @Route(path="/results", name="results", methods={"GET"})
     * @param ResultTable $resultTable
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     */
    public function indexAction(ResultTable $resultTable, FileCache $cache)
    {
        $results = $this->getDoctrine()->getRepository('App:Result')->findAll();

        $cacheKey = 'results' . count($results);

        $weekends = $cache->get($cacheKey);

        if (empty($weekends)) {
            foreach ($results as $result) {
                $weekends[$result->getEvent()->getWeekendOrder()][] = $resultTable
                    ->getTable($this->getUser(), $result->getEvent(), 'full')
                    ->renderTable($result->getEvent());
            }
            $cache->save($cacheKey, $weekends);
        }

        return $this->render('controller/page/results.html.twig', ['weekends' => $weekends]);
    }
}
