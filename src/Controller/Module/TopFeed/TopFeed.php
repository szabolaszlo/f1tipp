<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.29.
 * Time: 11:44
 */

namespace App\Controller\Module\TopFeed;

use App\Controller\Controller;
use Entity\Feed as FeedEntity;
use System\Cache\Cache;
use System\Registry\IRegistry;

/**
 * Class TopFeed
 * @package App\Controller\Module\Feed
 */
class TopFeed extends Controller
{
    const CACHE_ID = 'topFeed';

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Feed constructor.
     * @param IRegistry $registry
     */
    public function __construct(IRegistry $registry)
    {
        parent::__construct($registry);

        $this->cache = $this->registry->getCache();
    }

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
