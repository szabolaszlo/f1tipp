<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 28.
 * Time: 20:28
 */

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Result
 * @package App\Repository
 */
class Result extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getCalculatedRacesWithoutTrophies()
    {
        // adatbázisból döntjük el, nem a Race::$trophies gyűjteményből: az egy EntityManageren belül
        // nem frissül az új kupákkal, így egy második calculate() hívás duplán osztaná ki őket
        return $this->createQueryBuilder('result')
            ->select('race')
            ->innerJoin(\App\Entity\Race::class, 'race', Join::WITH, 'result.event = race.id')
            ->leftJoin(\App\Entity\Trophy::class, 'trophy', Join::WITH, 'trophy.event = race.id')
            ->where('result.isCalculated = 1')
            ->andWhere('trophy.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getCalculatedRacesWithoutAlternativeChampionship()
    {
        return $this->createQueryBuilder('result')
            ->select('race')
            ->innerJoin(\App\Entity\Race::class, 'race', Join::WITH, 'result.event = race.id')
            ->leftJoin(\App\Entity\AlternativeChampionship::class, 'ac', Join::WITH, 'ac.race = race.id')
            ->where('result.isCalculated = 1')
            ->andWhere('ac.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getNotCalculatedEvents()
    {
        return $this->createQueryBuilder('result')
            ->select('event')
            ->innerJoin(\App\Entity\Event::class, 'event', Join::WITH, 'result.event = event.id')
            ->where('result.isCalculated != 1')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Event $event
     * @return object
     * @throws NonUniqueResultException
     */
    public function getResultByEvent(Event $event)
    {
        return $this->createQueryBuilder('result')
            ->setCacheable(true)
            ->setParameter('givenEvent', $event->getId())
            ->where('result.event = :givenEvent')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getResultCount()
    {
        return $this->createQueryBuilder('result')
            ->select('count(result.id)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
