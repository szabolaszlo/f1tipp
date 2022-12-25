<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 11.
 * Time: 18:00
 */

namespace App\Controller\Page;

use App\Entity\Information;
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
     * @Route("/information_content/{slug}", name="information_content_show", methods={"GET"})
     * @param $slug
     * @return Response
     */
    public function showOnlyContentAction($slug): Response
    {
        return $this->render('controller/page/information_content.html.twig', [
            'information' => $this->getInformation($slug),
        ]);
    }

    /**
     * @param $slug
     * @return object|null
     */
    protected function getInformation($slug): ?object
    {
        $information = $this->getDoctrine()
            ->getRepository(Information::class)
            ->findOneBy(['slug' => $slug]);

        if (!$information) {
            throw new NotFoundHttpException();
        }

        return $information;
    }
}
