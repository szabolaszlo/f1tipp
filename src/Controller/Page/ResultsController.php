<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */

namespace App\Controller\Page;

use App\LegacyService\ResultTable\ResultTable;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
     * @return Response
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function indexAction(ResultTable $resultTable)
    {
        $results = $this->getDoctrine()->getRepository('App:Result')->findAll();

        $cacheKey = 'results' . count($results);

        $cache = new FilesystemAdapter();

        $cachedItem = $cache->getItem($cacheKey);

        if (!$cachedItem->isHit()) {
            $tables = (array)$cachedItem->get();

            foreach ($results as $result) {
                $tables[] = $resultTable
                    ->getTable($this->getUser(), $result->getEvent(), 'full')
                    ->renderTable($result->getEvent());
            }

            $cachedItem->set($tables);
            $cache->save($cachedItem);
        }

        return $this->render('controller/page/results.html.twig', ['tables' => $cachedItem->get()]);
    }
}
