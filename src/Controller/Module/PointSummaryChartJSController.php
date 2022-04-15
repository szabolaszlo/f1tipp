<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PointSummaryChartJSController extends AbstractController
{
    /**
     * @Route("/point_summary_chart_js", name="point_summary_chart_js", methods={"GET"})
     * @return Response
     */
    public function indexAction(): Response
    {
        $races = $this->getDoctrine()->getRepository('App:Race')->findAll();

        $pointHistory = [];
        $eventHistory = ['Start'];

        foreach ($races as $race) {
            $alter = $this
                ->getDoctrine()
                ->getRepository('App:AlternativeChampionship')
                ->getAlternativeChampionshipResultByRace($race);
            if (!empty($alter)) {
                $eventHistory[] = $race->getWeekendOrder() . ' - ' . $race->getName();
            }
            foreach ($alter as $a) {
                $lastPoint = isset($pointHistory[$a->getUser()->getName()]) ? end($pointHistory[$a->getUser()->getName()]) : 0;
                $pointHistory[$a->getUser()->getName()][] = $lastPoint + $a->getPoints();
            }
        }

        foreach ($pointHistory as $key => $value) {
            array_unshift($pointHistory[$key], 0);
        }

        $users = $this->getDoctrine()->getRepository('App:User')->getAlternativeChampionshipUsers();

        $sortMap = [];
        foreach ($users as $user) {
            $sortMap[$user->getName()] = $user->getAlternativePointSummary();
        }

        $pointHistorySorted = [];
        foreach ($sortMap as $name => $item) {
            if (isset($pointHistory[$name])) {
                $pointHistorySorted[$name] = $pointHistory[$name];
            }
        }

        return $this->render("controller/module/point_summary_chart_js.html.twig", [
            'id' => 'point_summary_chart',
            'eventHistory' => $eventHistory,
            'pointHistory' => $pointHistorySorted
        ]);
    }
}