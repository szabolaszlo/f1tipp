<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 22:17
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend\UserPoints;

use App\Entity\User;

/**
 * Class UserPoints
 * @package App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend\UserPoints
 */
class UserPoints
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var int
     */
    protected $collectedPoint;

    /**
     * @var int
     */
    protected $convertedPoint;

    /**
     * UserPoints constructor.
     * @param User $user
     * @param int $collectedPoint
     * @param int $convertedPoint
     */
    public function __construct(User $user, $collectedPoint, $convertedPoint)
    {
        $this->user = $user;
        $this->collectedPoint = $collectedPoint;
        $this->convertedPoint = $convertedPoint;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getCollectedPoint()
    {
        return $this->collectedPoint;
    }

    /**
     * @return int
     */
    public function getConvertedPoint()
    {
        return $this->convertedPoint;
    }
}
