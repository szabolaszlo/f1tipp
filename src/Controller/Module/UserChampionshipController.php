<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserChampionshipController
 * @package App\Controller\Module\UserChampionshipController
 */
class UserChampionshipController extends AbstractController
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
                'details_link' => $this->generateUrl('results'),
                'id' => 'userChampionship'
            ]
        );
    }
}
