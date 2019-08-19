<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 21:32
 */

namespace App\LegacyService\Rule\RuleType;

/**
 * Class Qualify
 * @package App\LegacyService\Rule
 */
class Qualify extends ARuleType
{
    /**
     * @var array
     */
    protected $betAbleAttributes = array(
        array('p1', 'driver', 4, 1),
        array('p2', 'driver', 4, 1),
        array('p3', 'driver', 4, 1),
        array('p4', 'driver', 4, 1),
        array('p5', 'driver', 4, 1),
        array('p6', 'driver', 4, 1),
        array('p7', 'driver', 4, 1),
        array('p8', 'driver', 4, 1),
        array('p9', 'driver', 4, 1),
        array('p10', 'driver', 4, 1)
    );
}
