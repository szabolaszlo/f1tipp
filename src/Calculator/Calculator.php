<?php

namespace App\Calculator;

use App\Calculator\Type\ICalculator;
use Doctrine\ORM\EntityManager;

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
     * @var EntityManager
     */
    protected $em;

    /**
     * Calculator constructor.
     * @param Registry $calculatorRegistry
     * @param EntityManager $em
     */
    public function __construct(Registry $calculatorRegistry, EntityManager $em)
    {
        $this->calculatorRegistry = $calculatorRegistry;
        $this->em = $em;
    }

    public function calculate()
    {
        $firstNotCalculatedEvent = $this->em->getRepository("App:Result")->getFirstNotCalculatedEvent();

        /** @var ICalculator $calculator */
        foreach ($this->calculatorRegistry->getCalculators() as $calculator) {
            if ($calculator->isNeedCalculate($firstNotCalculatedEvent)) {
                $calculator->calculate();
            }
        }
    }
}
