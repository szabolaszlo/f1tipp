<?php

namespace App\Controller\Page;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CalendarController
 * @package App\Controller\Page
 */
class CalendarController extends AbstractController
{
    /**
     * @Route("/calendar", name="calendar")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render("controller/page/calendar.html.twig", [
            'events' => $this->getDoctrine()->getRepository('App:Event')->getRemainEvents(),
            'type' => 'Event'
        ]);
    }

    /**
     * @Route("/calendar/qualify", name="calendar_qualify")
     * @return Response
     */
    public function qualifyAction()
    {
        return $this->render("controller/page/calendar.html.twig", [
            'events' => $this->getDoctrine()->getRepository('App:Qualify')->getRemainEvents(),
            'type' => 'Qualify'
        ]);
    }

    /**
     * @Route("/calendar/race", name="calendar_race")
     * @return Response
     */
    public function raceAction()
    {
        return $this->render("controller/page/calendar.html.twig", [
            'events' => $this->getDoctrine()->getRepository('App:Race')->getRemainEvents(),
            'type' => 'Race'
        ]);
    }
}
