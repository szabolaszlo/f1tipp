<?php

namespace App\Controller\Module;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActualController extends AbstractController
{
    /**
     * @return Response
     * @throws Exception
     */
    #[Route(path: '/module/actual', name: 'module_actual', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('controller/module/actual.html.twig', []);
    }
}
