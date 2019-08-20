<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2018.06.29.
 * Time: 23:14
 */

namespace App\Controller\Module\AlternativeChampionship;

use App\Controller\Page\WeekendPoints\WeekendPoints;

/**
 * Class AlternativeChampionship
 * @package App\Controller\Module\AlternativeChampionship
 */
class AlternativeChampionship extends WeekendPoints
{
    public function __construct()
    {
        $this->data['detailsLink'] = '/?page=weekendPoints/index';
    }
}
