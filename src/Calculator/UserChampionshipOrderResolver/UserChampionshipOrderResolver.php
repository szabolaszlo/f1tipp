<?php

namespace App\Calculator\UserChampionshipOrderResolver;

use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;

class UserChampionshipOrderResolver
{
    private UserRepository $userRepository;

    private EntityManagerInterface $entityManager;

    private $cacheKey = 'userChampionshipTrophyAwareOrder';

    /**
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getUserChampionShipOrder()
    {
        $userEntities = $this->userRepository->getAlternativeChampionshipUsers();

        $trophyAwareOrder = $this->getTrophyAwareOrder();

        $sortedUsers = [];
        foreach ($trophyAwareOrder as $row) {
            foreach ($userEntities as $user) {
                if ((int)$user->getId() === (int)$row['id']) {
                    $sortedUsers[] = $user;
                    break;
                }
            }
        }

        return $sortedUsers;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function getTrophyAwareOrder()
    {
        $cache = $this->entityManager->getConfiguration()->getResultCacheImpl();

        if ($cache->contains($this->cacheKey)) {
            return $cache->fetch($this->cacheKey);
        }

        $connection = $this->entityManager->getConnection();

        $sql = "
    SELECT
        u.id,
        u.name,
        u.alternative_point_summary,
        COUNT(CASE WHEN t.type = 'gold' THEN 1 END) AS gold_count,
        COUNT(CASE WHEN t.type = 'silver' THEN 1 END) AS silver_count,
        COUNT(CASE WHEN t.type = 'bronze' THEN 1 END) AS bronze_count
    FROM
        `user` u
    LEFT JOIN
        trophy t ON u.id = t.user
    GROUP BY
        u.id, u.name, u.alternative_point_summary
    ORDER BY
        u.alternative_point_summary DESC,
        gold_count DESC,
        silver_count DESC,
        bronze_count DESC
";

        $statement = $connection->prepare($sql);
        $executeQuery = $statement->executeQuery();
        $result = $executeQuery->fetchAllAssociative();

        $cache->save($this->cacheKey, $result);

        return $result;
    }

}
