<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserChampionship
 * @package App\Controller\Module\UserChampionship
 */
class UserChampionship extends AbstractController
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        return $this->render(
            'controller/module/user_championship/user_championship.html.twig',
            [
                'users' => $this
                    ->getDoctrine()
                    ->getRepository('App\Entity\User')
                    ->getUsersPointsSummaryDesc(),
                'records' => [
                    'qualify_bets' => $this->getDoctrine()->getRepository('App:Bet')->getTopQualifyBets(),
                    'race_bets' => $this->getDoctrine()->getRepository('App:Bet')->getTopRaceBets()
                ],
                'details_link' => '/trophies',
                'id' => 'userChampionship'
            ]
        );
    }
}
