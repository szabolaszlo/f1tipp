<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 18.
 * Time: 22:31
 */

namespace App\Controller\Module;

use App\LegacyService\UserAuthentication\Authentication;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Login
 * @package App\Controller\Module\Login
 */
class LoginController extends AbstractController
{
    /**
     * @Route(path="login/index", methods={"GET"})
     * @param SessionInterface $session
     * @param Authentication $authentication
     * @return string|Response
     */
    public function indexAction(SessionInterface $session, Authentication $authentication)
    {
        /** @var User $loggedUser */
        $loggedUser = $authentication->getLoggedUser();

        if ($loggedUser) {
            $authentication->updateExpire();
            return $this->loggedAction($loggedUser);
        }
        return $this->loginAction($session);
    }

    /**
     * @Route(path="login/login", methods={"GET"})
     * @param SessionInterface $session
     * @return Response
     */
    public function loginAction(SessionInterface $session)
    {
        $data['error'] = array(
            'error_user' => $session->get('error_user'),
            'error_password' => $session->get('error_password')
        );

        $data['userName'] = $session->get('user_name');

        $session->remove('error_user');
        $session->remove('error_password');
        $session->remove('user_name');

        return $this->render('controller/module/login.html.twig', $data);
    }

    /**
     * @Route(path="login/logged", methods={"GET"})
     * @param User $loggedUser
     * @return string
     */
    public function loggedAction(User $loggedUser)
    {
        $data['name'] = $loggedUser->getName();

        return $this->render('controller/module/logged.html.twig', $data);
    }

    /**
     * @Route(path="login/try_login", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @param Authentication $authentication
     * @return RedirectResponse
     */
    public function tryLoginAction(Request $request, SessionInterface $session, Authentication $authentication)
    {
        /** @var User $user */
        $user = $this->getDoctrine()
            ->getRepository('App\Entity\User')
            ->findOneBy(array('name' => $request->get('user-name')));

        if (!$user) {
            $session->set('error_user', true);
            return $this->redirectToRoute($request->get('actualPage', 'home'));
        }

        $storedPassword = $user->getPassword();

        if (!password_verify($request->get('password'), $storedPassword)) {
            $session->set('user_name', $user->getName());
            $session->set('error_password', true);
            return $this->redirectToRoute($request->get('actualPage', 'home'));
        }

        $authentication->setUserToLogged($user);

        return $this->redirectToRoute($request->get('actualPage', 'home'));
    }

    /**
     * @Route(path="login/logout", methods={"GET"})
     * @param Request $request
     * @param Authentication $authentication
     * @return RedirectResponse
     */
    public function logoutAction(Request $request, Authentication $authentication)
    {
        $authentication->destroyToken();
        return $this->redirectToRoute($request->get('actualPage', 'home'));
    }
}
