<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AlternativeChampionshipModuleController
 * @package App\Controller\Module
 */
class AlternativeChampionshipModuleController extends AbstractController
{
    //TODO: Design like Result of Championship
    /**
     * @Route("/module/alter_champs", name="module_alter_champs", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('controller/module/alternative_championship.html.twig', [
            'id' => 'alternative_championship_extension',
            'details_link' => $this->generateUrl('alternative_championship'),
            'users' => $this->getDoctrine()->getRepository('App:User')->getAlternativeChampionshipUsers()
        ]);
    }
}