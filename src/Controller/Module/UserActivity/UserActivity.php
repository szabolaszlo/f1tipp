<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 13.
 * Time: 20:31
 */

namespace Controller\Module\UserActivity;

use Controller\Controller;
use Entity\User;

/**
 * Class UserActivity
 * @package Controller\Module\UserActivity
 */
class UserActivity extends Controller
{
    protected $interval = 20; // Sec

    /**
     * @return string
     */
    public function indexAction()
    {
        $this->data['interval'] = $this->interval;
        return $this->render();
    }

    public function setUserOnlineAction()
    {
        /** @var User $user */
        $user = $this->registry->getUserAuth()->getLoggedUser();

        if ($user) {
            $user->setTimestamp(time());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function getOnlineUsersAction()
    {
        $users = $this->entityManager->getRepository('Entity\User')->findAll();

        $userNames = array();

        /** @var User $user */
        foreach ($users as $user) {
            if ($user->getTimestamp() >= (time() - $this->interval)) {
                $userNames[] = $user->getName();
            }
        }

        echo json_encode($userNames);
    }
}
