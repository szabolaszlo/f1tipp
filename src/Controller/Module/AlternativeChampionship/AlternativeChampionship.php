<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2018.06.29.
 * Time: 23:14
 */

namespace App\Controller\Module\AlternativeChampionship;

use App\Controller\Page\WeekendPoints\WeekendPoints;
use System\Registry\IRegistry;

/**
 * Class AlternativeChampionship
 * @package App\Controller\Module\AlternativeChampionship
 */
class AlternativeChampionship extends WeekendPoints
{
    public function __construct(IRegistry $registry)
    {
        parent::__construct($registry);

        $this->data['detailsLink'] = '/?page=weekendPoints/index';
    }
}
