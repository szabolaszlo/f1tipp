<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 11.
 * Time: 18:00
 */

namespace App\Controller\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class InformationController
 * @package App\Controller\Page\InformationController
 */
class InformationController extends AbstractController
{
    /**
     * @Route("/information/{slug}", name="information_show", methods={"GET"})
     * @param $slug
     * @return Response
     */
    public function showAction($slug)
    {
        $information = $this->getDoctrine()
            ->getRepository('App:Information')
            ->findOneBy(['slug' => $slug]);

        if (!$information) {
            throw new NotFoundHttpException();
        }

        return $this->render('controller/page/information.html.twig', ['information' => $information]);
    }
}
