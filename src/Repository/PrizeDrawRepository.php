<?php

namespace App\Repository;

use App\Entity\PrizeDraw;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PrizeDrawRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrizeDraw::class);
    }

    /**
     * Az egyetlen nyeremény-beállítás (second-level cache-ből); null, ha a sor hiányzik (nem futott le az SQL).
     */
    public function get(): ?PrizeDraw
    {
        return $this->find(PrizeDraw::ID);
    }
}
