<?php

namespace App\Calculator\Type;


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