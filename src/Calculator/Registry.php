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
     * @throws \Exception
     */
    public function addCalculator(ICalculator $calculator)
    {
        if (isset($this->calculators[$calculator->getSortOrder()])) {
            throw new \Exception("The Calculators must have to different order");
        }

        $this->calculators[$calculator->getSortOrder()] = $calculator;
    }

    /**
     * @return array
     */
    public function getCalculators()
    {
        ksort($this->calculators);
        return $this->calculators;
    }
}
