<?php

namespace App\Controller\Api;

use App\Entity\Trophy;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class TrophiesController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the last event podium trophies (sidebar module)
     * 
     * @Route("/trophies", name="trophies", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $lastTrophies = $this->entityManager
            ->getRepository(Trophy::class)
            ->getLastEventPodiumTrophies() ?? [];

        $podiumTrophies = [];
        $event = null;

        /** @var Trophy $trophy */
        foreach ($lastTrophies as $trophy) {
            $type = $trophy->getType();
            $podiumTrophies[$type][] = [
                'id' => $trophy->getId(),
                'type' => $type,
                'point' => $trophy->getPoint(),
                'user' => [
                    'id' => $trophy->getUser()->getId(),
                    'name' => $trophy->getUser()->getName(),
                ],
            ];
            $event = $trophy->getEvent();
        }

        return $this->json([
            'podiumTrophies' => $podiumTrophies,
            'event' => $event ? [
                'id' => $event->getId(),
                'name' => $event->getName(),
                'type' => $event->getType(),
            ] : null,
        ]);
    }

    /**
     * Get season trophies for all users (main page)
     * 
     * @Route("/season-trophies", name="season_trophies", methods={"GET"})
     */
    public function seasonTrophies(): JsonResponse
    {
        $trophyAttributes = [
            'gold' => 25,
            'silver' => 18,
            'bronze' => 15,
        ];

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->getAlternativeChampionshipUsers();

        $userTrophies = [];

        /** @var User $user */
        foreach ($users as $user) {
            $podiumTrophies = $user->getPodiumTrophies();

            $point = 0;
            $trophyCounts = [];

            foreach ($trophyAttributes as $type => $trophyPoint) {
                $count = 0;
                if (isset($podiumTrophies[$type]) && is_array($podiumTrophies[$type])) {
                    $count = count($podiumTrophies[$type]);
                    $point += $count * $trophyPoint;
                }
                $trophyCounts[$type] = $count;
            }

            $userTrophies[] = [
                'userId' => $user->getId(),
                'userName' => $user->getName(),
                'trophies' => $trophyCounts,
                'point' => $point,
            ];
        }

        // Sort by points descending
        usort($userTrophies, fn($a, $b) => $b['point'] - $a['point']);

        return $this->json([
            'userTrophies' => $userTrophies,
            'trophyAttributes' => $trophyAttributes,
        ]);
    }
}
