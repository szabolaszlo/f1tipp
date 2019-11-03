<?php

namespace App\Calculator\Type;

/**
 * Interface ICalculator
 * @package App\Calculator\Type
 */
interface ICalculator
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return boolean
     */
    public function isNeedCalculate();

    public function calculate();
}