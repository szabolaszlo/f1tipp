<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 13.
 * Time: 20:31
 */

namespace App\Controller\Module\UserActivity;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;

/**
 * Class UserActivity
 * @package App\Controller\Module\UserActivity
 */
class UserActivity extends AbstractController
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
        $users = $this->entityManager->getRepository('App\Entity\User')->findAll();

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
