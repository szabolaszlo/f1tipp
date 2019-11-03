<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 31.
 * Time: 21:18
 */

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Result;
use App\Entity\Trophy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TrophiesController
 * @package App\Controller\Module
 */
class TrophiesController extends AbstractController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $races = $this->getDoctrine()->getRepository('App:Result')->findByType('race');

        /** @var Result $result */
        $result = end($races);

        $trophies = $this->getDoctrine()->getRepository('App:Trophy')->findBy(
            array('event' => $result->getEvent())
        );

        $podiumTrophies = [];

        /** @var Trophy $trophy */
        foreach ($trophies as $trophy) {
            $podiumTrophies[$trophy->getType()][] = $trophy;
        }

        return $this->render(
            'controller/module/trophies/trophies.html.twig',
            [
                'podium_trophies' => $podiumTrophies,
                'event' => $result->getEvent(),
                'details_link' => '/trophies',
                'id' => 'trophies_module'
            ]
        );
    }
}
