<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 20:48
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\PointSummary;

use App\Controller\Page\WeekendPoints\ResultCollector\PointSummary\UserPoint\UserPoint;
use Doctrine\ORM\EntityManagerInterface;
use Entity\User;

/**
 * Class PointSummary
 * @package App\Controller\Page\WeekendPoints\ResultCollector\PointSummary
 */
class PointSummary
{
    /**
     * @var array
     */
    protected $allPoints = array();

    /**
     * @var
     */
    protected $repo;

    /**
     * PointSummary constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(User::class);
    }

    /**
     * @param $userWeekendPoints
     */
    public function addPoints($userWeekendPoints)
    {
        foreach ($userWeekendPoints as $userId => $point) {
            $this->allPoints[$userId] += $point;
        }
    }

    /**
     * @return array
     */
    public function getUserPoints()
    {
        arsort($this->allPoints);

        $array = array();

        foreach ($this->allPoints as $userId => $point) {
            $array[] = new UserPoint($this->repo->find($userId), $point);
        }

        return $array;
    }
}
