<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 19.
 * Time: 11:50
 */

namespace App\LegacyService\UserAuthentication;

use Application\HttpProtocol\ICookie;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\UserAuthentication;

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
     * @var ICookie
     */
    protected $cookie;

    /**
     * @var User
     */
    protected $loggedUser;

    /**
     * Authentication constructor.
     * @param EntityManagerInterface $entityManager
     * @param ICookie $cookie
     */
    public function __construct(EntityManagerInterface $entityManager, ICookie $cookie)
    {
        $this->entityManager = $entityManager;
        $this->cookie = $cookie;
        $this->userAuthEnt = $this->entityManager->getRepository('App\Entity\UserAuthentication');
    }

    /**
     * @return bool|User|mixed
     */
    public function getLoggedUser()
    {
        if (!$this->loggedUser) {
            $userToken = $this->cookie->get(self::TOKEN_NAME);

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
        if ($token === $this->cookie->get(self::TOKEN_NAME)) {
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

        $this->cookie->set(self::TOKEN_NAME, $newToken);
    }

    public function destroyToken()
    {
        $userToken = $this->cookie->get(self::TOKEN_NAME);
        $this->cookie->remove(self::TOKEN_NAME);

        /** @var UserAuthentication $existUserAuth */
        $existUserAuth = $this->userAuthEnt->findOneBy(array('token' => $userToken));

        if ($existUserAuth) {
            $this->entityManager->remove($existUserAuth);
            $this->entityManager->flush();
        }
    }

    public function updateExpire()
    {
        $userToken = $this->cookie->get(self::TOKEN_NAME);
        $this->cookie->set(self::TOKEN_NAME, $userToken, strtotime('+14 days'));

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
        return $this->cookie->get(self::TOKEN_NAME);
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
