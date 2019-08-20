<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */

namespace App\Controller\Page\Trophies;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use System\Cache\Cache;
/**
 * Class Results
 * @package App\Controller\Page\Results
 */
class Trophies extends AbstractController
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $trophyAttributes = array(
        'gold' => 25,
        'silver' => 18,
        'bronze' => 15
    );



    /**
     * @return mixed
     */
    public function indexAction()
    {
        $cachedContent = $this->cache->getCache($this->getCacheId());

        if ($cachedContent) {
            return $cachedContent;
        }

        $users = $this
            ->entityManager
            ->getRepository('App\Entity\User')
            ->findBy(array(), array('name' => 'ASC'));

        $userTrophies = array();

        /** @var User $user */
        foreach ($users as $user) {
            $userTrophies[$user->getName()] = $user->getPodiumTrophies();
        }

        $sortMap = array();

        foreach ($userTrophies as $user => $userTrophy) {
            $point = 0;
            foreach ($this->trophyAttributes as $type => $trophyPoint) {
                if (isset($userTrophy[$type]) && is_array($userTrophy[$type])) {
                    $point += count($userTrophy[$type]) * $trophyPoint;
                }
            }

            $sortMap[$user] = $point;
            $userTrophies[$user]['point'] = $point;
        }

        if (!empty(array_filter($sortMap))) {
            array_multisort($sortMap, SORT_DESC, $userTrophies, SORT_DESC);
        }

        $this->data['userTrophies'] = $userTrophies;
        $this->data['trophyAttributes'] = $this->trophyAttributes;

        $renderedContent = $this->render();

        $this->cache->setCache($this->getCacheId(), $renderedContent);

        return $renderedContent;
    }

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return 'trophies_page.' . count($this->entityManager->getRepository('App\Entity\Result')->findAll());
    }
}
