<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * SPA Fallback Controller
 * 
 * This controller catches all frontend routes and serves the base template,
 * allowing Vue Router to handle the routing client-side.
 */
class SpaController extends AbstractController
{
    /**
     * SPA Fallback - catch all frontend routes
     * 
     * Note: This route has low priority so API and other routes take precedence.
     * 
     * @Route("/{route}", name="spa_fallback", requirements={"route"="^(?!api|module|admin|logout|login|is_logged|get_google_oauth_url|auth).*"}, priority=-1000)
     */
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
