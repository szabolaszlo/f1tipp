<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.12.29.
 * Time: 17:53
 */

namespace App\Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter;

use App\Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter\PointProvider\PointProvider;

/**
 * Class CollectedPointConverter
 * @package App\Controller\Page\WeekendPoints\ResultCollector\CollectedPointConverter
 */
class CollectedPointConverter
{
    /**
     * @var PointProvider
     */
    protected $pointProvider;

    /**
     * CollectedPointConverter constructor.
     */
    public function __construct()
    {
        $this->pointProvider = new PointProvider();
    }

    /**
     * @param array $userCollectedPoints
     * @return array
     */
    public function convert(array $userCollectedPoints)
    {
        arsort($userCollectedPoints);

        $place = 1;
        $lastPoint = 0;
        $lastPlacePoint = 0;

        foreach ($userCollectedPoints as $userId => $userPoint) {
            if ($userPoint == $lastPoint) {
                $userCollectedPoints[$userId] = $lastPlacePoint;
            } else {
                $userCollectedPoints[$userId] = $lastPlacePoint = $this->pointProvider->getPlacePoint($place);
            }
            $lastPoint = $userPoint;
            $place++;
        }

        return $userCollectedPoints;
    }
}
