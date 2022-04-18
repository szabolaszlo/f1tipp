<?php

namespace App\Controller\Module;

use App\Twig\UserChampionshipTableRuntimeExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UserChampionshipController
 * @package App\Controller\Module\UserChampionshipController
 */
class UserChampionshipController extends AbstractController
{
    /**
     * @Route("/module/user_championship", name="module_user_championship", methods={"GET"})
     * @param UserChampionshipTableRuntimeExtension $championshipTableRuntimeExtension
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function indexAction(UserChampionshipTableRuntimeExtension $championshipTableRuntimeExtension): Response
    {
        return new Response($championshipTableRuntimeExtension->renderUserChampionshipTable());
    }
}
