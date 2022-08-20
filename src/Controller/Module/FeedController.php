<?php

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class FeedController
 * @package App\Controller\Module\FeedController
 */
class FeedController extends AbstractController
{
    const FEED_LIMIT = 11;

    /**
     * @return string
     */
    public function indexAction()
    {
        $feeds = $this->getDoctrine()
            ->getRepository('App:Feed')
            ->findBy(array(), array('id' => 'DESC'), self::FEED_LIMIT);

        return $this->render('controller/module/feed/feed.html.twig', [
            'id'=>'feed',
            'feeds' => $feeds
        ]);
    }
}
