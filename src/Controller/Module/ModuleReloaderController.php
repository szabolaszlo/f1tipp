<?php

namespace App\Controller\Module;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModuleReloaderController
 * @package App\Controller\Module
 */
class ModuleReloaderController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @Route("/module/entity_counts", name="entity_counts", methods={"GET"})
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function entityCountsAction(): Response
    {
        return $this->json([
            'results' => (int)$this->doctrine->getRepository(\App\Entity\Result::class)->getResultCount(),
            'bets' => (int)$this->doctrine->getRepository(\App\Entity\Bet::class)->getBetCount(),
            'trophies' => (int)$this->doctrine->getRepository(\App\Entity\Trophy::class)->getTrophyCount(),
            'messages' => (int)$this->doctrine->getRepository(\App\Entity\Message::class)->getMessageCount(),
        ], 200);

    }
}
