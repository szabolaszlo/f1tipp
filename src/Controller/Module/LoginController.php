<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 18.
 * Time: 22:31
 */

namespace App\Controller\Module;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class Login
 * @package App\Controller\Module\Login
 */
class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return string|Response
     */
    public function indexAction(AuthenticationUtils $authenticationUtils)
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();

        if ($loggedUser) {
            return $this->render('controller/module/logged.html.twig', ['name' => $loggedUser->getName()]);
        }

        $data['error'] = $authenticationUtils->getLastAuthenticationError();

        $data['last_username'] = $authenticationUtils->getLastUsername();

        return $this->render('controller/module/login.html.twig', $data);
    }

    /**
     * @Route("logout", name="app_logout")
     * @throws \Exception
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
