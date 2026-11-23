<?php

namespace App\Repository;

use App\Entity\ChampionshipPoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChampionshipPoint[] findAll()
 */
class ChampionshipPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChampionshipPoint::class);
    }

    /**
     * A pontozási tábla (second-level query cache-ből): helyezés => bajnoki pont, helyezés szerint növekvő.
     *
     * @return array<int, int>
     */
    public function getPlacePoints(): array
    {
        /** @var ChampionshipPoint[] $rows */
        $rows = $this->createQueryBuilder('championship_point')
            ->setCacheable(true)
            ->orderBy('championship_point.place', 'ASC')
            ->getQuery()
            ->getResult();

        $placePoints = [];
        foreach ($rows as $row) {
            $placePoints[$row->getPlace()] = $row->getPoints();
        }

        return $placePoints;
    }
}
