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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class Login
 * @package App\Controller\Module\Login
 */
class LoginController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return JsonResponse
     */
    #[Route(path: '/login', name: 'app_login')]
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

    #[Route(path: 'is_logged', name: 'is_logged')]
    public function isLoggedAction(): JsonResponse
    {
        return $this->json([
            'userName' => $this->getUser() ? $this->getUser()->getUserIdentifier() : null,
            // a role_hierarchy miatt a ROLE_SUPER_ADMIN is ide tartozik; a fejléc Admin gombja ebből dönt
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
        ], $this->getUser() ? 200 : 401);
    }

    #[Route(path: 'get_google_oauth_url', name: 'get_google_oauth_url')]
    public function getGoogleAuthLinkAction(Google $google): JsonResponse
    {
        return $this->json(
            ['google_oauth_link' => $google->getAuthorizationUrl()]
        );
    }

    /**
     * @return Response
     */
    #[Route(path: 'auth/google', name: 'auth_google')]
    public function authGoogleAction()
    {
        throw new \LogicException('All google authentication should be handled in GoogleAuthenticator.');
    }

    /**
     * @throws \Exception
     */
    #[Route(path: 'logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
