<?php

namespace App\Controller\Module;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function entityCountsAction(): Response
    {
        return $this->json([
            'results' => (int)$this->getDoctrine()->getRepository('App:Result')->getResultCount(),
            'bets' => (int)$this->getDoctrine()->getRepository('App:Bet')->getBetCount(),
            'trophies' => (int)$this->getDoctrine()->getRepository('App:Trophy')->getTrophyCount(),
            'messages' => (int)$this->getDoctrine()->getRepository('App:Message')->getMessageCount(),
        ], 200);

    }
}
