<?php

namespace App\Calculator\Type;

use App\Calculator\ChampionshipPointConverter;
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

    protected ChampionshipPointConverter $converter;

    public function __construct(EntityManagerInterface $em, PointProvider $alternativePointsProvider, ChampionshipPointConverter $converter)
    {
        parent::__construct($em);
        $this->alternativePointsProvider = $alternativePointsProvider;
        $this->converter = $converter;
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
            ->getRepository(\App\Entity\Result::class)
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
        $weekendEvents = $this->em->getRepository(\App\Entity\Event::class)->getWeekendEvents($race);
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
     * A hétvége résztvevőinek bajnoki pontja a hétvégi összpont sorrendje és a pontozási tábla alapján.
     * A ChampionshipPointRecalculator ugyanezt hívja, amikor az adminban megváltozik a tábla.
     */
    public function calculateUsersAlternativePoints(Race $race): void
    {
        /** @var AlternativeChampionship[] $alterChamps */
        $alterChamps = $this->em->getRepository(\App\Entity\AlternativeChampionship::class)->getByRaceCollectedPointsOrdered($race);

        $points = $this->converter->convert(
            array_map(fn (AlternativeChampionship $ac) => $ac->getCollectedPoints(), $alterChamps),
            $this->alternativePointsProvider->getPlacePoints()
        );

        foreach ($alterChamps as $index => $ac) {
            $ac->setPoints($points[$index]);
            $this->em->persist($ac);
        }
    }
}
