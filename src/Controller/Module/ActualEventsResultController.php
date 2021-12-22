<?php

namespace App\Controller\Module;

use App\Entity\Event;
use App\LegacyService\ResultTable\ResultTable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ActualEventsResultController
 * @package App\Controller\Page
 */
class ActualEventsResultController extends AbstractController
{
    /**
     * @Route("/module/actual_events_result", name="actual_events_result", methods={"GET"})
     * @param ResultTable $resultTable
     * @return Response
     * @throws Exception
     */
    public function indexAction(ResultTable $resultTable): Response
    {
        $events = array(
            $this->getDoctrine()->getRepository('App:Qualify')->getNextEvent(),
            $race = $this->getDoctrine()->getRepository('App:Race')->getNextEvent(),
        );

        $tables = [];

        $now = new \DateTime();

        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());
            $tables[$id] = $resultTable->getTable($this->getUser(), $event)->renderTable($event);
        }

        //summary
        $id = abs($now->getTimestamp() - ($race->getDateTime()->getTimeStamp() + 1));
        $tables[$id] = $resultTable
            ->getTable($this->getUser(), $race, 'summary')
            ->renderTable($race);

        ksort($tables);

        return $this->render('controller/module/actual_events_results.html.twig', [
            'tables' => $tables
        ]);
    }
}
