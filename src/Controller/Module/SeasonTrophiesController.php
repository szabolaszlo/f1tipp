<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class SeasonTrophiesController extends AbstractController
{
    /**
     * @var array
     */
    protected $trophyAttributes = array(
        'gold' => 25,
        'silver' => 18,
        'bronze' => 15
    );

    /**
     * @Route("/module/season-trophies", name="trophies", methods={"GET"})
     * @return string|Response
     */
    public function indexAction()
    {
        $users = $this
            ->getDoctrine()
            ->getRepository('App:User')
            ->getAlternativeChampionshipUsers();

        $userTrophies = array();

        /** @var User $user */
        foreach ($users as $user) {
            $userTrophies[$user->getName()] = $user->getPodiumTrophies();
        }

        $sortMap = array();

        foreach ($userTrophies as $user => $userTrophy) {
            $point = 0;
            foreach ($this->trophyAttributes as $type => $trophyPoint) {
                if (isset($userTrophy[$type]) && is_array($userTrophy[$type])) {
                    $point += count($userTrophy[$type]) * $trophyPoint;
                }
            }

            $sortMap[$user] = $point;
            $userTrophies[$user]['point'] = $point;
        }

        if (!empty(array_filter($sortMap))) {
            array_multisort($sortMap, SORT_DESC, $userTrophies, SORT_DESC);
        }

        return $this->render('controller/module/season_trophies.html.twig',
            [
                'user_trophies' => $userTrophies,
                'trophy_attributes' => $this->trophyAttributes,
                'id'=> 'trophies'
            ]
        );
    }
}
