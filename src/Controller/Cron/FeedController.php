<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.27.
 * Time: 20:33
 */

namespace App\Controller\Cron;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FeedController
 * @package App\Controller\Page\FeedController
 */
class FeedController extends Controller
{
    /**
     * @Route("cron/collect_feeds", name="cron_collect_feeds", methods={"GET"})
     * @return Response
     * @throws Exception
     */
    public function collectAction()
    {
        $feedsEntity = $this->getDoctrine()
            ->getRepository('App:Feed')
            ->findBy(array(), array('id' => 'DESC'), 1);

        $feedHandler = $this->get('app.legacy_service.feed.handler');

        $feeds = $feedHandler->getItems();

        if ($this->getLastFeedId($feedsEntity) != $this->getLastFeedId($feeds)) {
            $feedHandler->saveItems($feeds);
            $this->getDoctrine()->getRepository('App:Feed')->deleteOldFeeds();
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
