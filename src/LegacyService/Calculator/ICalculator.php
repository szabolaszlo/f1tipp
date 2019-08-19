<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 29.
 * Time: 15:51
 */

namespace App\LegacyService\Calculator;

use App\Entity\Bet;
use App\Entity\Race;
use App\Entity\Result;
use App\Entity\User;

/**
 * Interface ICalculator
 * @package App\LegacyService\Calculator
 */
interface ICalculator
{
    /**
     * @param Bet $bet
     * @param Result $result
     */
    public function calculateBetPoints(Bet $bet, Result $result);

    /**
     * @param User $user
     */
    public function calculateUserPoints(User $user);

    /**
     * @param $type
     * @return array|mixed
     */
    public function getRecordsByType($type);

    /**
     * @param Race $race
     * @return array
     */
    public function calculateUserPointsByCompleteWeekend(Race $race);
}
