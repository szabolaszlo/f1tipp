<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 10:15
 */

namespace App\LegacyService\Calculator\RecordCollector\Record;

/**
 * Class Record
 * @package App\LegacyService\Calculator\RecordCollector\Record
 */
class Record
{
    /**
     * @var string
     */
    protected $userName = '';

    /**
     * @var int
     */
    protected $point = 0;

    /**
     * @var int
     */
    protected $times = 1;

    /**
     * Record constructor.
     * @param string $userName
     * @param int $point
     */
    public function __construct($userName, $point)
    {
        $this->userName = $userName;
        $this->point = $point;
    }
    
    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return int
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param int $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return int
     */
    public function getTimes()
    {
        return $this->times;
    }

    public function setTimes($times)
    {
        $this->times = $times;
    }

    public function addTimes()
    {
        $this->times++;
    }
}
