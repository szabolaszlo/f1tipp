<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 21:32
 */

namespace App\Rule\RuleType;

/**
 * Class Qualify
 * @package App\Rule
 */
class Race extends ARule
{
    /**
     * @var string
     */
    protected $type = 'race';

    /**
     * @var array
     */
    protected $betAbleAttributes = array(
        array('p1', 'driver', 3, 1),
        array('p2', 'driver', 3, 1),
        array('p3', 'driver', 3, 1),
        array('p4', 'driver', 3, 1),
        array('p5', 'driver', 3, 1),
        array('p6', 'driver', 3, 1),
        array('p7', 'driver', 3, 1),
        array('p8', 'driver', 3, 1),
        array('p9', 'driver', 3, 1),
        array('p10', 'driver', 3, 1),
        array('sc', 'question', 3, 0)
    );
}
