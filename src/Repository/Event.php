<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 18:15
 */

namespace App\Repository;

use App\Entity\Event as EventEnt;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Qualify
 * @package App\Repository
 */
class Event extends EntityRepository
{
    /**
     * @return array|mixed
     */
    public function getNextEvent()
    {
        $resultCache = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheKey = $this->_entityName . 'NextEvent';

        if ($resultCache->contains($cacheKey)) {
            return $resultCache->fetch($cacheKey);
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('P2D'));

        $nextEvent = $this->createQueryBuilder('e')
            ->where('e.date_time > :time')
            ->setMaxResults(1)
            ->setParameter('time', $date)
            ->getQuery()
            ->getResult();

        if (empty($nextEvent) || !$nextEvent) {
            $nextEvent = parent::findBy(array(), array('weekendOrder' => 'DESC'), 1);
        }

        $nextEvent = reset($nextEvent);

        $lifeTime = strtotime('+2 days', $nextEvent->getDateTime()->getTimeStamp()) - time();

        $resultCache->save($cacheKey, $nextEvent, $lifeTime);

        return $nextEvent;
    }

    /**
     * @return array|mixed
     */
    public function getRemainEvents()
    {
        $resultCache = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheKey = $this->_entityName . 'Remain';

        if ($resultCache->contains($cacheKey)) {
            return $resultCache->fetch($cacheKey);
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('PT2H'));

        $remainEvents = $this->createQueryBuilder('e')
            ->where('e.date_time > :time')
            ->setParameter('time', $date)
            ->getQuery()
            ->getResult();

        if (!empty($remainEvents)) {
            $resultCache->save(
                $cacheKey,
                $remainEvents,
                strtotime('+2 hours', $remainEvents[0]->getDateTime()->getTimeStamp()) - time()
            );
        }
        return $remainEvents;
    }

    /**
     * @param EventEnt $event
     * @return array
     */
    public function getWeekendEvents(EventEnt $event)
    {
        return $this->findBy(
            ['weekendOrder' => $event->getWeekendOrder()]
        );
    }

    /**
     * @return mixed
     */
    public function getAlternativeChampionshipRaces()
    {
        return $this->createQueryBuilder('race')
            ->select('race')
            ->innerJoin('App:AlternativeChampionship', 'ac', Join::WITH, 'ac.race = race.id')
            ->orderBy('race.weekendOrder', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
