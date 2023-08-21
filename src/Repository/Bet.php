<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
    public function getBetsByEvent($event): array
    {
        return $this->createQueryBuilder('betsByEvent')
            ->setCacheable(true)
            ->where('betsByEvent.event_id = ' . $event->getId())
            ->getQuery()
            ->getResult();
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
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getBetByUserAndEvent($user, $event)
    {
        return $this->createQueryBuilder('betByUserAndEvent')
            ->setCacheable(true)
            ->where('betByUserAndEvent.user_id = ' . $user->getId())
            ->andWhere('betByUserAndEvent.event_id = ' . $event->getId())
            ->getQuery()
            ->getResult();
    }

    public function getBetByUserAndEventNonCache($user, $event)
    {
        return $this->findOneBy([
            'user_id' => $user,
            'event_id' => $event->getId()
        ]);
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

    public function getBetsByEventOrderByPoints($event)
    {
        return $this->findBy(
            ['event_id' => $event->getId()],
            ['pointSummary' => 'DESC']
        );
    }

    /**
     * @return mixed
     */
    public function getTopRaceBets()
    {
        $resultCache = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheKey = 'topRaceBets';

        if ($resultCache->contains($cacheKey)) {
            return $resultCache->fetch($cacheKey);
        }

        $result = $this->createQueryBuilder('topRaceBet')
            ->setCacheable(true)
            ->innerJoin('App:Race', 'race', Join::WITH, 'topRaceBet.event_id = race.id')
            ->innerJoin('App:User', 'user', Join::WITH, 'topRaceBet.user_id = user.id AND user.isAlterChamps = 1')
            ->where('topRaceBet.pointSummary > 0')
            ->orderBy('topRaceBet.pointSummary', 'DESC')
            ->getQuery()
            ->getResult();

        $pointsGroups = [];

        foreach ($result as $user) {
            $points = $user->getPointSummary();

            if (!isset($pointsGroups[$points])) {
                $pointsGroups[$points] = [];
            }

            $pointsGroups[$points][] = $user->getUser()->getName();
        }

        foreach ($pointsGroups as $points => $users) {
            $pointsGroups[$points] = implode(', ', $users);
        }

        $pointsGroups = array_slice($pointsGroups, 0, 3, true);

        $resultCache->save($cacheKey, $pointsGroups);

        return $pointsGroups;
    }

    /**
     * @return mixed
     */
    public function getTopQualifyBets()
    {
        $resultCache = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheKey = 'topQualifyBets';

        if ($resultCache->contains($cacheKey)) {
            return $resultCache->fetch($cacheKey);
        }

        $result = $this->createQueryBuilder('topQualifyBet')
            ->setCacheable(true)
            ->innerJoin('App:Qualify', 'qualify', Join::WITH, 'topQualifyBet.event_id = qualify.id')
            ->innerJoin('App:User', 'user', Join::WITH, 'topQualifyBet.user_id = user.id AND user.isAlterChamps = 1')
            ->where('topQualifyBet.pointSummary > 0')
            ->orderBy('topQualifyBet.pointSummary', 'DESC')
            ->getQuery()
            ->getResult();

        $pointsGroups = [];

        foreach ($result as $user) {
            $points = $user->getPointSummary();

            if (!isset($pointsGroups[$points])) {
                $pointsGroups[$points] = [];
            }

            $pointsGroups[$points][] = $user->getUser()->getName();
        }

        foreach ($pointsGroups as $points => $users) {
            $pointsGroups[$points] = implode(', ', $users);
        }

        $pointsGroups = array_slice($pointsGroups, 0, 3, true);

        $resultCache->save($cacheKey, $pointsGroups);

        return $pointsGroups;
    }

    /**
     * @param Event $event
     * @return mixed
     */
    public function clearBetPointsByEvent(Event $event)
    {
        return $this->createQueryBuilder('bet')
            ->update('App:Bet', 'b')
            ->set('b.pointSummary', 'NULL')
            ->where('b.event_id = :e')
            ->setParameter('e', $event->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getBetCount()
    {
        return $this->createQueryBuilder('bet')
                ->select('count(bet.id)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
    }
}
