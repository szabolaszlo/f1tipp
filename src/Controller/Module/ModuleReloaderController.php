<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModuleReloaderController
 * @package App\Controller\Module
 */
class ModuleReloaderController extends AbstractController
{
    /**
     * @Route("/module/entity_counts", name="entity_counts", methods={"GET"})
     * @return Response
     */
    public function entityCountsAction()
    {
        return $this->json([
            'results' => count($this->getDoctrine()->getRepository('App:Result')->findAll()),
            'bets' => count($this->getDoctrine()->getRepository('App:Bet')->findAll()),
            'trophies' => count($this->getDoctrine()->getRepository('App:Trophy')->findAll()),
            'messages' => count($this->getDoctrine()->getRepository('App:Message')->findAll()),
            'alter_champs' => count($this->getDoctrine()->getRepository('App:AlternativeChampionship')->findAll())
        ], 200);

    }
}