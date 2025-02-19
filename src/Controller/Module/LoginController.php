<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 18.
 * Time: 22:31
 */

namespace App\Controller\Module;

use App\Entity\User;
use League\OAuth2\Client\Provider\Google;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return JsonResponse
     */
    public function indexAction(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        /** @var User $loggedUser */
        $loggedUser = $this->getUser();

        if ($loggedUser) {
            return $this->json([]);
        }

        $exception = $authenticationUtils->getLastAuthenticationError();

        return $this->json(
            ['error' => $exception ? get_class($exception) : ''],
            401
        );
    }

    /**
     * @Route("is_logged", name="is_logged")
     */
    public function isLoggedAction(): JsonResponse
    {
        return $this->json([
            'userName' => $this->getUser() ? $this->getUser()->getUsername() : null
        ], $this->getUser() ? 200 : 401);
    }

    /**
     * @Route("get_google_oauth_url", name="get_google_oauth_url")
     */
    public function getGoogleAuthLinkAction(Google $google): JsonResponse
    {
        return $this->json(
            ['google_oauth_link' => $google->getAuthorizationUrl()]
        );
    }

    /**
     * @Route ("auth/google", name="auth_google")
     * @return Response
     */
    public function authGoogleAction()
    {
        throw new \LogicException('All google authentication should be handled in GoogleAuthenticator.');
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
