<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 19.
 * Time: 11:50
 */

namespace App\LegacyService\UserAuthentication;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\UserAuthentication;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Authentication
 * @package App\LegacyService\User\Authentication
 */
class Authentication
{
    const TOKEN_NAME = 'f1ipp_user_token';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserAuthentication
     */
    protected $userAuthEnt;

    /**
     * @var ParameterBag
     */
    protected $cookies;

    /**
     * @var User
     */
    protected $loggedUser;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Authentication constructor.
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->cookies = $requestStack->getMasterRequest()->cookies;
        $this->userAuthEnt = $this->entityManager->getRepository('App\Entity\UserAuthentication');
        $this->response = new Response();
    }

    /**
     * @return bool|User|mixed
     */
    public function getLoggedUser()
    {
        if (!$this->loggedUser) {
            $userToken = $this->cookies->get(self::TOKEN_NAME);

            /** @var UserAuthentication $existUserAuth */
            $existUserAuth = $this->userAuthEnt->findOneBy(array('token' => $userToken));

            $this->loggedUser = $existUserAuth ? $existUserAuth->getUser() : false;
        }

        return $this->loggedUser;
    }

    /**
     * @param $token
     * @return bool|User|mixed
     */
    public function getUserByToken($token)
    {
        if ($token === $this->cookies->get(self::TOKEN_NAME)) {
            return $this->getLoggedUser();
        }

        return false;
    }

    /**
     * @param User $user
     */
    public function setUserToLogged(User $user)
    {
        $newToken = bin2hex(openssl_random_pseudo_bytes(16));

        $newUserAuthEnt = new UserAuthentication();
        $newUserAuthEnt->setToken($newToken);
        $newUserAuthEnt->setUser($user);

        $this->entityManager->persist($newUserAuthEnt);
        $this->entityManager->flush();

        $this->loggedUser = $user;

        setcookie(self::TOKEN_NAME, $newToken);
    }

    public function destroyToken()
    {
        $userToken = $this->cookies->get(self::TOKEN_NAME);
        $this->cookies->remove(self::TOKEN_NAME);

        /** @var UserAuthentication $existUserAuth */
        $existUserAuth = $this->userAuthEnt->findOneBy(array('token' => $userToken));

        if ($existUserAuth) {
            $this->entityManager->remove($existUserAuth);
            $this->entityManager->flush();
        }
    }

    public function updateExpire()
    {
        $userToken = $this->cookies->get(self::TOKEN_NAME);

        setcookie(self::TOKEN_NAME, $userToken, strtotime('+14 days'));

        /** @var UserAuthentication $existUserAuth */
        $existUserAuth = $this->userAuthEnt->findOneBy(array('token' => $userToken));

        if ($existUserAuth) {
            $date = new \DateTime();
            $date->add(new \DateInterval('P14D'));

            $existUserAuth->setTokenExpire($date);
            $this->entityManager->persist($existUserAuth);
            $this->entityManager->flush();
        }
    }

    /**
     * @return string
     */
    public function getActualToken()
    {
        return $this->cookies->get(self::TOKEN_NAME);
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        $user = $this->getLoggedUser();
        return (bool)($user && $user->isAdmin());
    }
}
