<?php

namespace App\Repository;

use App\Entity\Race;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Bet
 * @package App\Repository
 */
class Bet extends EntityRepository
{
    /**
     * @param $event
     * @return array
     */
    public function getBetsByEvent($event)
    {
        return $this->findBy(['event_id' => $event]);
    }

    /**
     * @param $user
     * @return array
     */
    public function getBetsByUser($user)
    {
        return $this->findBy(['user_id' => $user]);
    }

    /**
     * @param User $user
     * @param array $events
     * @return array
     */
    public function getBetByUserAndEvents(User $user, array $events)
    {
        return $this->findBy([
            'user_id' => $user,
            'event_id' => $events
        ]);
    }

    /**
     * @return mixed
     */
    public function getTopRaceBets()
    {
        return $this->createQueryBuilder('bet')
            ->innerJoin('App:Race', 'race', Join::WITH, 'bet.event_id = race.id')
            ->orderBy('bet.pointSummary', 'DESC')
            ->getQuery()
            ->setMaxResults(3)
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getTopQualifyBets()
    {
        return $this->createQueryBuilder('bet')
            ->innerJoin('App:Qualify', 'qualify', Join::WITH, 'bet.event_id = qualify.id')
            ->orderBy('bet.pointSummary', 'DESC')
            ->getQuery()
            ->setMaxResults(3)
            ->getResult();
    }
}
