<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.29.
 * Time: 11:44
 */

namespace App\Controller\Module\TopFeed;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Feed as FeedEntity;
use System\Cache\Cache;

/**
 * Class TopFeed
 * @package App\Controller\Module\Feed
 */
class TopFeed extends AbstractController
{
    const CACHE_ID = 'topFeed';

    /**
     * @var Cache
     */
    protected $cache;



    /**
     * @return string
     */
    public function indexAction()
    {
        $cachedContent = $this->cache->getCache(self::CACHE_ID);

        if ($cachedContent) {
            return $cachedContent;
        }

        $feeds = $this->entityManager
            ->getRepository(FeedEntity::class)
            ->findBy(array(), array('id' => 'DESC'), 1);

        $this->data['feed'] = is_array($feeds) && isset($feeds[0]) ? $feeds[0] : array();

        $renderedContent = $this->render();

        $this->cache->setCache(self::CACHE_ID, $renderedContent);

        return $renderedContent;
    }
}
