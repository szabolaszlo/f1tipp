<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 18.
 * Time: 22:31
 */

namespace App\Controller\Module\Login;

use Application\HttpProtocol\IServer;
use App\Controller\Controller;
use Entity\User;
use System\Registry\IRegistry;
use System\UserAuthentication\Authentication;

/**
 * Class Login
 * @package App\Controller\Module\Login
 */
class Login extends Controller
{
    /**
     * @var Authentication
     */
    protected $authentication;

    /**
     * @var IServer
     */
    protected $server;

    /**
     * Login constructor.
     * @param IRegistry $registry
     */
    public function __construct(IRegistry $registry)
    {
        parent::__construct($registry);

        $this->authentication = $this->registry->getUserAuth();
        $this->server = $this->registry->getServer();

        $this->data['actualPage'] = 'page=' . $this->registry->getRequest()->getQuery('page', 'actual/index');
    }

    /**
     * @return mixed
     */
    public function indexAction()
    {
        /** @var User $loggedUser */
        $loggedUser = $this->authentication->getLoggedUser();

        if ($loggedUser) {
            $this->authentication->updateExpire();
            return $this->loggedAction($loggedUser);
        }
        return $this->loginAction();
    }

    /**
     * @return string
     */
    public function loginAction()
    {
        $this->data['error'] = array(
            'error_user' => $this->session->get('error_user'),
            'error_password' => $this->session->get('error_password')
        );

        $this->data['userName'] = $this->session->get('user_name');

        $this->session->remove('error_user');
        $this->session->remove('error_password');
        $this->session->remove('user_name');

        return $this->render();
    }

    /**
     * @param User $loggedUser
     * @return string
     */
    public function loggedAction(User $loggedUser)
    {
        $this->data['name'] = $loggedUser->getName();

        $this->setTemplate('controller/module/login/logged.tpl');

        return $this->render();
    }

    public function tryLoginAction()
    {
        /** @var User $user */
        $user = $this->entityManager
            ->getRepository('Entity\User')
            ->findOneBy(array('name' => $this->request->getPost('user-name')));

        if (!$user) {
            $this->session->set('error_user', true);
            $this->server->redirect('module=login/login&' . $this->data['actualPage']);
        }

        $storedPassword = $user->getPassword();

        if (!password_verify($this->request->getPost('password'), $storedPassword)) {
            $this->session->set('user_name', $user->getName());
            $this->session->set('error_password', true);
            $this->server->redirect('module=login/login&' . $this->data['actualPage']);
        }

        $this->authentication->setUserToLogged($user);

        $this->server->redirect($this->data['actualPage']);
    }

    public function logoutAction()
    {
        $this->authentication->destroyToken();
        $this->server->redirect($this->data['actualPage']);
    }
}
