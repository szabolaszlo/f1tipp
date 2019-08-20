<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 21:01
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\PointSummary\UserPoint;

use Entity\User;

/**
 * Class UserPoint
 * @package App\Controller\Page\WeekendPoints\ResultCollector\PointSummary\UserPoint
 */
class UserPoint
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var int
     */
    protected $point = 0;

    /**
     * UserPoint constructor.
     * @param User $user
     * @param int $point
     */
    public function __construct(User $user, $point)
    {
        $this->user = $user;
        $this->point = $point;
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
    public function getPoint()
    {
        return $this->point;
    }
}
