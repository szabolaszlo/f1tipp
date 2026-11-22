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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class UserActivityController
 * @package App\Controller\Module\UserActivityController
 */
class UserActivityController extends AbstractController
{
    private const ONLINE_WINDOW_SECONDS = 60;

    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @param FileCache $cache
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(path: 'get_online_user', name: 'get_online_user', methods: ['GET'])]
    public function getOnlineUsersAction(FileCache $cache): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user) {
            $cache->save($user->getName() . 'Online', [time()]);
        }

        $users = $this->doctrine->getRepository(\App\Entity\User::class)->getAlternativeChampionshipUsers();

        $userNames = array();

        /** @var User $user */
        foreach ($users as $user) {
            $timeStamp = $cache->get($user->getName() . 'Online');
            // a kliens 20 mp-enként kérdez (assets/js/app.js): az ablak ennél jóval nagyobb, hogy egy késve
            // beérkező lekérés (háttérfül, lassú hálózat) miatt ne villogjon szürkére egy aktív user
            if (reset($timeStamp) >= (time() - self::ONLINE_WINDOW_SECONDS)) {
                $userNames[] = $user->getName();
            }
        }

        return $this->json($userNames, 200);
    }
}
