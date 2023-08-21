<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getTrophyCount()
    {
        return $this->createQueryBuilder('trophy')
                ->select('count(trophy.id)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
    }

    public function getTopWeekendUsers()
    {
        $resultCache = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheKey = 'topWeekendUsers';

        if ($resultCache->contains($cacheKey)) {
            return $resultCache->fetch($cacheKey);
        }

        $result = $this->createQueryBuilder('topWeekendUsers')
            ->setCacheable(true)
            ->innerJoin('App:User', 'user', Join::WITH, 'topWeekendUsers.user = user.id AND user.isAlterChamps = 1')
            ->where('topWeekendUsers.point > 0')
            ->orderBy('topWeekendUsers.point', 'DESC')
            ->getQuery()
            ->getResult();

        $pointsGroups = [];

        foreach ($result as $trophy) {
            $points = $trophy->getPoint();

            if (!isset($pointsGroups[$points])) {
                $pointsGroups[$points] = [];
            }

            $pointsGroups[$points][] = $trophy->getUser()->getName();
        }

        foreach ($pointsGroups as $points => $users) {
            $pointsGroups[$points] = implode(', ', $users);
        }

        $pointsGroups = array_slice($pointsGroups, 0, 3, true);

        $resultCache->save($cacheKey, $pointsGroups);

        return $pointsGroups;
    }

}
