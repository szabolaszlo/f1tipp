<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 13.
 * Time: 20:31
 */

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserActivityController
 * @package App\Controller\Module\UserActivityController
 */
class UserActivityController extends AbstractController
{
    /**
     * @Route("set_online_user", name="set_online_user", methods={"GET"})
     * @return JsonResponse
     */
    public function setUserOnlineAction()
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            $user->setTimestamp(time());

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->json('OK', 200);
    }

    /**
     * @Route("get_online_user", name="get_online_user", methods={"GET"})
     * @return JsonResponse
     */
    public function getOnlineUsersAction()
    {
        $users = $this->getDoctrine()->getRepository('App:User')->findAll();

        $userNames = array();

        /** @var User $user */
        foreach ($users as $user) {
            if ($user->getTimestamp() >= (time() - 20)) {
                $userNames[] = $user->getName();
            }
        }

        return $this->json($userNames, 200);
    }
}
