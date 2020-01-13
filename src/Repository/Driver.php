<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class Driver
 * @package App\Repository
 */
class Driver extends EntityRepository
{
    /**
     * @return array
     */
    public function getDriverChoices()
    {
        $drivers = $this->findBy(
            ['status' => '1'],
            ['point' => 'DESC']
        );

        $choices = [];

        /** @var \App\Entity\Driver $driver */
        foreach ($drivers as $driver) {
            $choices['[' . $driver->getShort() . '] - ' . $driver->getName()] = $driver->getShort();
        }

        return $choices;
    }
}
