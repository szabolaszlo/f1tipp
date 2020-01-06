<?php

namespace App\Controller\Module;

use App\Entity\Event;
use App\LegacyService\ResultTable\ResultTable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ActualEventsResultController
 * @package App\Controller\Page
 */
class ActualEventsResultController extends AbstractController
{
    /**
     * @param ResultTable $resultTable
     * @return Response
     * @throws \Exception
     */
    public function indexAction(ResultTable $resultTable)
    {
        $events = array(
            $this->getDoctrine()->getRepository('App:Qualify')->getNextEvent(),
            $this->getDoctrine()->getRepository('App:Race')->getNextEvent()
        );

        $tables = [];

        $now = new \DateTime();

        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());
            $tables[$id] = $resultTable->getTable($this->getUser(), $event)->renderTable($event);
        }

        ksort($tables);

        return $this->render('controller/module/actual_events_results.html.twig', [
            'tables' => $tables
        ]);
    }
}