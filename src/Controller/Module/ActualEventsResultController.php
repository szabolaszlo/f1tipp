<?php

namespace App\Controller\Module;

use App\Twig\ActualEventsRuntimeExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ActualEventsResultController
 * @package App\Controller\Page
 */
class ActualEventsResultController extends AbstractController
{
    /**
     * @Route("/module/actual_events_result", name="actual_events_result", methods={"GET"})
     * @param ActualEventsRuntimeExtension $actualEventsRuntimeExtension
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function indexAction(ActualEventsRuntimeExtension $actualEventsRuntimeExtension): Response
    {
        return new Response($actualEventsRuntimeExtension->renderActualEvents($this->getUser()));
    }
}
