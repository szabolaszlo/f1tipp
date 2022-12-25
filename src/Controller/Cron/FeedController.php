<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 20:33
 */

namespace App\Controller\Cron;

use App\Entity\Feed;
use App\LegacyService\Feed\Handler;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FeedController
 * @package App\Controller\Page\FeedController
 */
class FeedController extends AbstractController
{
    /**
     * @var Handler
     */
    private Handler $feedHandler;

    /**
     * @param Handler $feedHandler
     */
    public function __construct(Handler $feedHandler)
    {
        $this->feedHandler = $feedHandler;
    }

    /**
     * @Route("cron/collect_feeds", name="cron_collect_feeds", methods={"GET"})
     * @param ManagerRegistry $managerRegistry
     * @return Response
     * @throws Exception
     */
    public function collectAction(ManagerRegistry $managerRegistry): Response
    {
        $feedsEntity = $managerRegistry
            ->getRepository(Feed::class)
            ->findBy(array(), array('id' => 'DESC'), 1);

        $feeds = $this->feedHandler->getItems();

        if ($this->getLastFeedId($feedsEntity) != $this->getLastFeedId($feeds)) {
            $this->feedHandler->saveItems($feeds);
            $managerRegistry->getRepository(Feed::class)->deleteOldFeeds();
        }

        return new Response('OK', 200);
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
