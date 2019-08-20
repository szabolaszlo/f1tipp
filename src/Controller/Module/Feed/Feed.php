<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.28.
 * Time: 14:17
 */

namespace App\Controller\Module\Feed;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Feed as FeedEntity;
use System\Cache\Cache;

/**
 * Class Feed
 * @package App\Controller\Module\Feed
 */
class Feed extends AbstractController
{
    const FEED_LIMIT = 31;

    const CACHE_ID = 'feeds';

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

        $this->data['feeds'] = $this->entityManager
            ->getRepository(FeedEntity::class)
            ->findBy(array(), array('id' => 'DESC'), self::FEED_LIMIT);

        if (is_array($this->data['feeds']) && isset($this->data['feeds'][0])) {
            unset($this->data['feeds'][0]);
        }

        $renderedContent = $this->render();

        $this->cache->setCache(self::CACHE_ID, $renderedContent);

        return $renderedContent;
    }
}
