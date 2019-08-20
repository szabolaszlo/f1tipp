<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 22:01
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend;

use App\Entity\Race;

/**
 * Class Weekend
 * @package App\Controller\Page\WeekendPoints\ResultCollector\WeekendSummary\Weekend
 */
class Weekend
{
    /**
     * @var array
     */
    protected $usersPoints;

    /**
     * @var Race
     */
    protected $event;

    /**
     * Weekend constructor.
     * @param Race $event
     * @param $userPoints
     */
    public function __construct(Race $event, $userPoints)
    {
        $this->event = $event;
        $this->usersPoints = $userPoints;
    }

    /**
     * @return array
     */
    public function getUsersPoints()
    {
        return $this->usersPoints;
    }

    /**
     * @return Race
     */
    public function getEvent()
    {
        return $this->event;
    }
}
