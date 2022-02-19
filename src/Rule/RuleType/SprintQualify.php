<?php

namespace App\Rule\RuleType;

class SprintQualify extends ARule
{
    /**
     * @var string
     */
    protected $type = 'sprint_qualify';

    /**
     * @var array
     */
    protected $betAbleAttributes = array(
        array('p1', 'driver', 1, 0),
        array('p2', 'driver', 1, 0),
        array('p3', 'driver', 1, 0),
        array('p4', 'driver', 1, 0),
        array('p5', 'driver', 1, 0),
        array('p6', 'driver', 1, 0),
        array('p7', 'driver', 1, 0),
        array('p8', 'driver', 1, 0)
    );
}
