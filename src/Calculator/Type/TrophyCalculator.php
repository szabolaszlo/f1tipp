<?php

namespace App\Calculator\Type;

use App\Entity\Bet;
use App\Entity\Race;
use App\Entity\Trophy;
use App\Entity\User;
use Doctrine\ORM\ORMException;

/**
 * Class TrophyCalculator
 * @package App\Calculator\Type
 */
class TrophyCalculator extends ACalculator
{
    const PODIUM_FIRST = 'gold';
    const PODIUM_SECOND = 'silver';
    const PODIUM_THIRD = 'bronze';

    /**
     * @var
     */
    protected $calculatedRacesWithoutTrophies;

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return 1;
    }

    /**
     * @return bool
     */
    public function isNeedCalculate()
    {
        $this->calculatedRacesWithoutTrophies = $this->em
            ->getRepository('App:Result')
            ->getCalculatedRacesWithoutTrophies();
        return (bool)(count($this->calculatedRacesWithoutTrophies));
    }

    /**
     * @throws ORMException
     */
    public function calculate()
    {
        /** @var Race $race */
        foreach ($this->calculatedRacesWithoutTrophies as $race){
            $users = $this->collectUsersWeekendPoints($race);
            $podium = $this->getPodiumUsers($users);
            $this->setTrophiesByPodium($race, $podium);
        }

        $this->em->flush();
    }

    /**
     * @param Race $race
     * @return User[]|array|object[]
     */
    protected function collectUsersWeekendPoints(Race $race)
    {
        $weekendEvents = $this->em->getRepository("App:Event")->getWeekendEvents($race);
        $users = $this->em->getRepository('App\Entity\User')->findAll();

        foreach ($users as $user) {
            $userPoints = 0;
            $bets = $this->em->getRepository('App\Entity\Bet')->getBetByUserAndEvents($user, $weekendEvents);

            /** @var Bet $bet */
            foreach ($bets as $bet) {
                $userPoints += $bet->getPointSummary();
            }
            $user->setPoint($userPoints);
        }

        return $users;
    }

    /**
     * @param array $users
     * @return array
     */
    protected function getPodiumUsers($users = array())
    {
        $userPoints = array();
        $podium = array(
            self::PODIUM_FIRST => array(),
            self::PODIUM_SECOND => array(),
            self::PODIUM_THIRD => array()
        );

        /** @var User $user */
        foreach ($users as $user) {
            $userPoints[$user->getId()] = $user->getPoint();
        }

        foreach ($podium as $key => $value) {
            $podium[$key] = array_flip(array_keys($userPoints, max($userPoints)));
            foreach ($podium[$key] as $userId => $pValue) {
                $podium[$key][$userId] = $userPoints[$userId];
                unset($userPoints[$userId]);
            }
        }

        return $podium;
    }

    /**
     * @param Race $race
     * @param array $podium
     * @throws ORMException
     */
    protected function setTrophiesByPodium(Race $race, $podium = array())
    {
        foreach ($podium as $trophyType => $users) {
            foreach ($users as $userId => $userPoint) {
                $this->setTrophy($race, $trophyType, $userId, $userPoint);
            }
        }
    }

    /**
     * @param Race $race
     * @param $trophyType
     * @param $userId
     * @param $userPoint
     * @throws ORMException
     */
    protected function setTrophy(Race $race, $trophyType, $userId, $userPoint)
    {
        $user = $this->em->getRepository('App\Entity\User')->find($userId);

        $trophy = new Trophy();
        $trophy->setEvent($race);
        $trophy->setUser($user);
        $trophy->setPoint($userPoint);
        $trophy->setType($trophyType);

        $this->em->persist($trophy);
    }
}