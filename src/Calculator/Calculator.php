<?php

namespace App\Calculator;

use App\Calculator\Type\ICalculator;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Calculator constructor.
     * @param Registry $calculatorRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(Registry $calculatorRegistry, EntityManagerInterface $em)
    {
        $this->calculatorRegistry = $calculatorRegistry;
        $this->em = $em;
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
