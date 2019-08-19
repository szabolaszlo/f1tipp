<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 23:38
 */

namespace App\LegacyService\Feed\Repository;

/**
 * Class MotorSportRepository
 * @package App\LegacyService\Feed\Repository
 */
interface IRepository
{
    /**
     * @return array
     */
    public function getItems();
}
