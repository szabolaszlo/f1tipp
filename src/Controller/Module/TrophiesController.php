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
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TrophiesController
 * @package App\Controller\Module
 */
class TrophiesController extends AbstractController
{
    /**
     * @Route("/module/trophies", name="module_trophies", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $races = $this->getDoctrine()->getRepository('App:Result')->findByType('race');

        $podiumTrophies = [];

        $event = false;

        if (!empty($races)) {
            /** @var Result $result */
            $result = end($races);

            $event = $result->getEvent();

            $trophies = $this->getDoctrine()->getRepository('App:Trophy')->findBy(
                array('event' => $event)
            );

            /** @var Trophy $trophy */
            foreach ($trophies as $trophy) {
                $podiumTrophies[$trophy->getType()][] = $trophy;
            }
        }

        return $this->render(
            'controller/module/trophies.html.twig',
            [
                'podium_trophies' => $podiumTrophies,
                'event' => $event,
                'details_link' => '/trophies',
                'id' => 'trophies_module'
            ]
        );
    }
}
