<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 20:33
 */

namespace App\Controller\Page\Feed;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\Module\Feed\Feed as FeedModule;
use App\Controller\Module\TopFeed\TopFeed;
use App\Entity\Feed as FeedEntity;
use App\Entity\Repository\Feed as FeedRepo;
use System\Cache\Cache;
use System\Feed\Handler;
use System\Feed\Repository\MotorSportRepository;
use System\Feed\Storage\Doctrine;

/**
 * Class Feed
 * @package App\Controller\Page\Feed
 */
class Feed extends AbstractController
{
    /**
     * @var Cache
     */
    protected $cache;


    public function collectAction()
    {
        $feedsEntity = $this->entityManager
            ->getRepository(FeedEntity::class)
            ->findBy(array(), array('id' => 'DESC'), 1);

        $repositories = array(new MotorSportRepository());

        $storage = new Doctrine($this->entityManager);

        $handler = new Handler($repositories, $storage);

        $feeds = $handler->getItems();

        if ($this->getLastFeedId($feedsEntity) != $this->getLastFeedId($feeds)) {
            $handler->saveItems($feeds);

            $this->cache->removeCache(FeedModule::CACHE_ID);
            $this->cache->removeCache(TopFeed::CACHE_ID);
        }

        echo 'Done';
        exit(200);
    }

    public function cleanAction()
    {
        /** @var FeedRepo $repo */
        $repo = $this->entityManager->getRepository(FeedEntity::class);
        $repo->deleteOldFeeds();
        echo 'Done';
        exit(200);
    }

    /**
     * @param $feeds
     * @return null
     */
    protected function getLastFeedId($feeds)
    {
        $lastStoredFeedId = null;

        if (is_array($feeds) && isset($feeds[0])) {
            $feed = $feeds[0];
            $lastStoredFeedId = $feed->getId();
        }

        return $lastStoredFeedId;
    }
}
