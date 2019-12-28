<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */

namespace App\Controller\Page;

use App\LegacyService\ResultTable\ResultTable;
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
     * @return Response
     * @throws \Exception
     */
    public function indexAction(ResultTable $resultTable)
    {
        $results = $this->getDoctrine()->getRepository('App:Result')->findAll();

        $tables = [];

        foreach ($results as $result) {
            $tables[] = $resultTable
                ->getTable($this->getUser(), $result->getEvent(), 'full')
                ->renderTable($result->getEvent());
        }

        return $this->render('controller/page/results.html.twig', ['tables' => $tables]);
    }
}
