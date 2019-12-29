<?php

namespace App\Calculator\Type;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ACalculator
 * @package App\Calculator\Type
 */
abstract class ACalculator implements ICalculator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * TrophyCalculator constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return bool
     */
    public function isNeedCalculate()
    {
        return true;
    }
}
