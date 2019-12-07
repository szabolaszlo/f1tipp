<?php

namespace App\Calculator\Type;

use App\Entity\Event;

/**
 * Interface ICalculator
 * @package App\Calculator\Type
 */
interface ICalculator
{
    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @return bool
     */
    public function isNeedCalculate();

    public function calculate();
}