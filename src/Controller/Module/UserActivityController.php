<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 13.
 * Time: 20:31
 */

namespace App\Controller\Module;

use App\Cache\FileCache;
use Psr\Cache\InvalidArgumentException;
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
     * @Route("get_online_user", name="get_online_user", methods={"GET"})
     * @param FileCache $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function getOnlineUsersAction(FileCache $cache): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            $cache->save($user->getName() . 'Online', [time()]);
        }

        $users = $this->getDoctrine()->getRepository('App:User')->getAlternativeChampionshipUsers();

        $userNames = array();

        /** @var User $user */
        foreach ($users as $user) {
            $timeStamp = $cache->get($user->getName() . 'Online');
            if (reset($timeStamp) >= (time() - 20)) {
                $userNames[] = $user->getName();
            }
        }

        return $this->json($userNames, 200);
    }
}
