<?php

namespace App\Calculator\Type;

use Doctrine\ORM\EntityManager;

/**
 * Class ACalculator
 * @package App\Calculator\Type
 */
abstract class ACalculator implements ICalculator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * TrophyCalculator constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}
