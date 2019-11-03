<?php

namespace App\Calculator;

use App\Calculator\Type\ICalculator;

/**
 * Class Registry
 * @package App\Calculator
 */
class Registry
{
    /**
     * @var array
     */
    protected $calculators = [];

    /**
     * @param ICalculator $calculator
     */
    public function addCalculator(ICalculator $calculator)
    {
        $this->calculators[$calculator->getType()] = $calculator;
    }

    /**
     * @return array
     */
    public function getCalculators()
    {
        return $this->calculators;
    }
}
