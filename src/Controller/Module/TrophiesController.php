<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 31.
 * Time: 21:18
 */

namespace App\Controller\Module;

use App\Twig\TrophyRuntimeExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TrophiesController
 * @package App\Controller\Module
 */
class TrophiesController extends AbstractController
{
    /**
     * @Route("/module/trophies", name="module_trophies", methods={"GET"})
     * @param TrophyRuntimeExtension $trophyRuntimeExtension
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function indexAction(TrophyRuntimeExtension $trophyRuntimeExtension): Response
    {
        return new Response($trophyRuntimeExtension->renderTrophyModule());
    }
}
