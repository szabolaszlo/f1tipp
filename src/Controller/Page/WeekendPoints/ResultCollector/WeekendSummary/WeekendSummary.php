<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 21:39
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary;

use App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend\UserPoints\UserPoints;
use App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend\Weekend;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Race;
use App\Entity\User;

/**
 * Class WeekendSummary
 * @package App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary
 */
class WeekendSummary
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var PointMerger
     */
    protected $pointMerger;

    /**
     * @var array
     */
    protected $weekends;

    /**
     * WeekendSummary constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addWeekend(array $collectedPoints, array $convertedPoints, Race $race)
    {
        $usersPoints = array();

        foreach ($convertedPoints as $userId => $point) {
            $usersPoints[] = new UserPoints(
                $this->entityManager->getRepository(User::class)->find($userId),
                $collectedPoints[$userId],
                $point
            );
        }

        $this->weekends[] = new Weekend(
            $race,
            $usersPoints
        );
    }

    /**
     * @return array
     */
    public function getWeekends()
    {
        return array_reverse($this->weekends);
    }
}
