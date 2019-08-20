<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 21:22
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector;

use App\Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter\CollectedPointConverter;
use App\Controller\Page\WeekendPoints\ResultCollector\PointSummary\PointSummary;
use App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\WeekendSummary;
use Doctrine\ORM\EntityManagerInterface;
use Entity\Race;
use Entity\Result;
use Entity\User;
use System\Calculator\ICalculator;

class ResultCollector
{
    /**
     * @var ICalculator
     */
    protected $calculator;

    /**
     * @var CollectedPointConverter
     */
    protected $pointConverter;

    /**
     * @var PointSummary
     */
    protected $pointSummary;

    /**
     * @var WeekendSummary
     */
    protected $weekendSummary;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected $acceptedUsers = array(
        1, 2, 3, 6, 7
    );

    /**
     * ResultCollector constructor.
     * @param EntityManagerInterface $entityManager
     * @param ICalculator $calculator
     */
    public function __construct(EntityManagerInterface $entityManager, ICalculator $calculator)
    {
        $this->entityManager = $entityManager;

        $this->calculator = $calculator;
        $this->pointConverter = new CollectedPointConverter();
        $this->pointSummary = new PointSummary($this->entityManager);
        $this->weekendSummary = new WeekendSummary($this->entityManager);
    }

    public function collect()
    {
        $results = $this->entityManager->getRepository('Entity\Result')->findByType('race');

        /** @var Result $result */
        foreach ($results as $result) {
            /** @var Race $race */
            $race = $result->getEvent();

            $this->collectByRace($race);
        }
    }

    /**
     * @param Race $race
     */
    protected function collectByRace(Race $race)
    {
        $userCollectedPoints = array();

        $users = $this->calculator->calculateUserPointsByCompleteWeekend($race);

        /** @var User $user */
        foreach ($users as $user) {
            if (in_array($user->getId(), $this->acceptedUsers)) {
                $userCollectedPoints[$user->getId()] = $user->getPoint();
            }
        }

        $userConvertedPoints = $this->pointConverter->convert($userCollectedPoints);

        $this->pointSummary->addPoints($userConvertedPoints);

        $this->weekendSummary->addWeekend($userCollectedPoints, $userConvertedPoints, $race);
    }

    /**
     * @return array
     */
    public function getPoints()
    {
        return $this->pointSummary->getUserPoints();
    }

    /**
     * @return array
     */
    public function getWeekends()
    {
        return $this->weekendSummary->getWeekends();
    }
}
