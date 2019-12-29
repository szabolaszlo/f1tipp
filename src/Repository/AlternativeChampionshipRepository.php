<?php

namespace App\Repository;

use App\Entity\AlternativeChampionship;
use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AlternativeChampionship|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlternativeChampionship|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlternativeChampionship[]    findAll()
 * @method AlternativeChampionship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlternativeChampionshipRepository extends ServiceEntityRepository
{
    /**
     * AlternativeChampionshipRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlternativeChampionship::class);
    }

    /**
     * @param Race $race
     * @return AlternativeChampionship[]
     */
    public function getByRaceCollectedPointsOrdered(Race $race)
    {
        return $this->findBy(
            ['race' => $race],
            ['collectedPoints' => 'DESC']
        );
    }

    /**
     * @param $user
     * @return array
     */
    public function getBetsByUser($user)
    {
        return $this->findBy(['user' => $user]);
    }
}
