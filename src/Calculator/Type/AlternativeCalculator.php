<?php

namespace App\Calculator\Type;

use App\Calculator\Provider\PointProvider;
use App\Entity\AlternativeChampionship;
use App\Entity\Bet;
use App\Entity\Race;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AlternativeCalculator
 * @package App\Calculator\Type
 */
class AlternativeCalculator extends ACalculator
{
    /**
     * @var array
     */
    protected $calcedRacesWoutAlterChamps = [];

    /**
     * @var PointProvider
     */
    protected $alternativePointsProvider;

    /**
     * AlternativeCalculator constructor.
     * @param EntityManagerInterface $em
     * @param PointProvider $alternativePointsProvider
     */
    public function __construct(EntityManagerInterface $em, PointProvider $alternativePointsProvider)
    {
        parent::__construct($em);
        $this->alternativePointsProvider = $alternativePointsProvider;
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return 3;
    }

    /**
     * @inheritDoc
     */
    public function isNeedCalculate()
    {
        $this->calcedRacesWoutAlterChamps = $this->em
            ->getRepository('App:Result')
            ->getCalculatedRacesWithoutAlternativeChampionship();
        return (bool)(count($this->calcedRacesWoutAlterChamps));
    }

    public function calculate()
    {
        /** @var Race $race */
        foreach ($this->calcedRacesWoutAlterChamps as $race) {
            $this->calculateUsersWeekendPoints($race);
            $this->calculateUsersAlternativePoints($race);
        }

        $this->em->flush();
    }


    /**
     * @param Race $race
     * @return User[]|array|object[]
     */
    protected function calculateUsersWeekendPoints(Race $race)
    {
        $weekendEvents = $this->em->getRepository("App:Event")->getWeekendEvents($race);
        $users = $this->em->getRepository('App\Entity\User')->getAlternativeChampionshipUsers();

        foreach ($users as $user) {
            $userPoints = 0;
            $bets = $this->em->getRepository('App\Entity\Bet')->getBetByUserAndEvents($user, $weekendEvents);

            /** @var Bet $bet */
            foreach ($bets as $bet) {
                $userPoints += $bet->getPointSummary();
            }

            $ac = new AlternativeChampionship();
            $ac->setUser($user);
            $ac->setRace($race);
            $ac->setCollectedPoints($userPoints);
            $this->em->persist($ac);
        }

        $this->em->flush();
    }

    /**
     * @param Race $race
     * @return User[]|array|object[]
     */
    protected function calculateUsersAlternativePoints(Race $race)
    {
        $alterChamps = $this->em->getRepository("App:AlternativeChampionship")->getByRaceCollectedPointsOrdered($race);

        $place = 1;
        $lastPoint = 0;
        $lastPlacePoint = 0;

        /** @var AlternativeChampionship $ac */
        foreach ($alterChamps as $ac) {
            if ($ac->getCollectedPoints() == $lastPoint) {
                $ac->setPoints($lastPlacePoint);
            } else {
                $lastPlacePoint = $this->alternativePointsProvider->getPlacePoint($place);
                $ac->setPoints($lastPlacePoint);
            }
            $lastPoint = $ac->getCollectedPoints();
            $place++;

            $this->em->persist($ac);
        }
    }
}
