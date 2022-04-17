<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class Trophy extends EntityRepository
{
    public function getLastEventPodiumTrophies()
    {
        return $this->createQueryBuilder('trophy')
            ->setCacheable(true)
            ->select('trophy')
            ->innerJoin('trophy.event', 'race', Join::WITH, 'trophy.event = race')
            ->orderBy('trophy.point', 'DESC')
            ->where(
                $this->_em->getExpressionBuilder()
                    ->in('race.weekendOrder',
                        $this->createQueryBuilder('trophy2')
                            ->setCacheable(true)
                            ->select('MAX(race2.weekendOrder)')
                            ->innerJoin('trophy2.event', 'race2', Join::WITH, 'trophy2.event = race2')
                            ->getDQL()
                    )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $events
     * @return Event
     */
    public function removeTrophiesByEvents($events)
    {
        $eventIds = [];
        foreach ($events as $event) {
            $eventIds[] = $event->getId();
        }

        return $this->createQueryBuilder('trophy')
            ->delete('App:Trophy', 't')
            ->where('t.event IN (:e)')
            ->setParameter('e', $eventIds)
            ->getQuery()
            ->execute();
    }
}
