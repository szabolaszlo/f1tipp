<?php

namespace App\Controller\Page;

use App\Calculator\Provider\PointProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AlternativeChampionshipController
 * @package App\Controller\Page
 */
class AlternativeChampionshipController extends AbstractController
{
    /**
     * @Route("/alternative_championship", name="alternative_championship", methods={"GET"})
     * @param PointProvider $pointProvider
     * @return Response
     */
    public function indexAction(PointProvider $pointProvider)
    {
        //TODO Cache This
        return $this->render('controller/page/alternative_champinoship.html.twig', [
            'id' => 'alternativeChampionship',
            'users' => $this->getDoctrine()->getRepository("App:User")->getAlternativeChampionshipUsers(),
            'races' => $this->getDoctrine()->getRepository('App:Race')->getAlternativeChampionshipRaces(),
            'pointProvider' => $pointProvider
        ]);
    }
}