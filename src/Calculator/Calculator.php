<?php

namespace App\Calculator;

use App\Calculator\Type\ICalculator;

/**
 * Class Calculator
 * @package App\Calculator
 */
class Calculator
{
    /**
     * @var Registry
     */
    protected $calculatorRegistry;

    /**
     * Calculator constructor.
     * @param Registry $calculatorRegistry
     */
    public function __construct(Registry $calculatorRegistry)
    {
        $this->calculatorRegistry = $calculatorRegistry;
    }

    public function calculate()
    {
        /** @var ICalculator $calculator */
        foreach ($this->calculatorRegistry->getCalculators() as $calculator) {
            if ($calculator->isNeedCalculate()) {
                $calculator->calculate();
            }
        }
    }
}
