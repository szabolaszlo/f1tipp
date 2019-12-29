<?php

namespace App\Calculator\Type;

use App\Entity\AlternativeChampionship;
use App\Entity\User;

/**
 * Class AlternativePointSummaryCalculator
 * @package App\Calculator\Type
 */
class AlternativePointSummaryCalculator extends ACalculator
{
    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return 4;
    }

    public function calculate()
    {
        $users = $this->em->getRepository('App:User')->getAlternativeChampionshipUsers();
        $sortMap = array();

        /** @var User $user */
        foreach ($users as $key => $user) {
            $userPoints = 0;
            //TODO Change this foreach, mysql SUM
            $acs = $this->em->getRepository('App:AlternativeChampionship')->getBetsByUser($user);
            /** @var AlternativeChampionship $ac */
            foreach ($acs as $ac) {
                $userPoints += $ac->getPoints();
            }
            $user->setAlternativePointSummary($userPoints);
            $sortMap[] = $user->getAlternativePointSummary();
        }

        array_multisort($sortMap, SORT_DESC, $users, SORT_DESC);

        /** @var User $user */
        foreach ($users as $user) {
            $pointDifference = (isset($sortMap[0]) && $sortMap[0] - $user->getAlternativePointSummary())
                ? ($sortMap[0] - $user->getAlternativePointSummary())
                : null;

            $user->setAlternativePointDifference($pointDifference);
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}