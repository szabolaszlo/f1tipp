<?php

namespace App\Calculator\Type;

use App\Entity\Event;

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
     * @param Event $firstNotCalculatedEvent
     * @return bool
     */
    public function isNeedCalculate(Event $firstNotCalculatedEvent);

    public function calculate();
}