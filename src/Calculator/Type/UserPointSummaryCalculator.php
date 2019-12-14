<?php

namespace App\Calculator\Type;

use App\Entity\Bet;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class UserPointSummaryCalculator
 * @package App\Calculator\Type
 */
class UserPointSummaryCalculator implements ICalculator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * UserPointSummaryCalculator constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return 2;
    }

    /**
     * @return bool
     */
    public function isNeedCalculate()
    {
        return true;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function calculate()
    {
        $users = $this->em->getRepository('App:User')->findAll();
        $sortMap = array();

        foreach ($users as $user) {
            $userPoints = 0;
            $bets = $this->em->getRepository('App:Bet')->getBetsByUser($user);
            /** @var Bet $bet */
            foreach ($bets as $bet) {
                $userPoints += $bet->getPointSummary();
            }
            $user->setPointSummary($userPoints);
            $sortMap[] = $user->getPointSummary();
        }

        array_multisort($sortMap, SORT_DESC, $users, SORT_DESC);

        /** @var User $user */
        foreach ($users as $user) {
            $pointDifference = (isset($sortMap[0]) && $sortMap[0] - $user->getPointSummary())
                ? ($sortMap[0] - $user->getPointSummary())
                : null;

            $user->setPointDifference($pointDifference);
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}