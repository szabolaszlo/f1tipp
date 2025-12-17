<?php

namespace App\Controller\Api;

use App\Calculator\Provider\PointProvider;
use App\Calculator\UserChampionshipOrderResolver\UserChampionshipOrderResolver;
use App\Entity\Bet;
use App\Entity\Race;
use App\Entity\Trophy;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class UserChampionshipController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserChampionshipOrderResolver $userChampionshipOrderResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserChampionshipOrderResolver $userChampionshipOrderResolver
    ) {
        $this->entityManager = $entityManager;
        $this->userChampionshipOrderResolver = $userChampionshipOrderResolver;
    }

    /**
     * Get user championship standings
     * 
     * @Route("/user-championship", name="user_championship", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $users = $this->userChampionshipOrderResolver->getUserChampionShipOrder();
        $pointProvider = new PointProvider();

        $usersData = [];
        /** @var User $user */
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'pointSummary' => $user->getAlternativePointSummary() ?? $user->getPointSummary(),
                'pointDifference' => $user->getAlternativePointDifference() ?? $user->getPointDifference(),
            ];
        }

        $records = [
            'qualifyBets' => $this->entityManager->getRepository(Bet::class)->getTopQualifyBets(),
            'raceBets' => $this->entityManager->getRepository(Bet::class)->getTopRaceBets(),
            'weekendBets' => $this->entityManager->getRepository(Trophy::class)->getTopWeekendUsers(),
        ];

        $remainingWeekends = $this->entityManager->getRepository(Race::class)->getRemainEvents();

        return $this->json([
            'users' => $usersData,
            'records' => $records,
            'maths' => [
                'remainingWeekends' => $remainingWeekends,
                'maxPointsPerWeekend' => $pointProvider->getMaxPoint(),
            ],
        ]);
    }
}
