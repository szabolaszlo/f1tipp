<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 29.
 * Time: 16:00
 */

namespace App\LegacyService\ResultTable\Decorator;

use App\Entity\Bet;

/**
 * Interface IDecorator
 * @package App\LegacyService\ResultTable\Decorator
 */
interface IDecorator
{
    /**
     * @param Bet $bet
     */
    public function decorate(Bet $bet);
}
