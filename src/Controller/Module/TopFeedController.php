<?php
/**
 * Created by PhpStorm.
 * User: szabolaszlo
 * Date: 2017.10.29.
 * Time: 11:44
 */

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TopFeedController
 * @package App\Controller\Module
 */
class TopFeedController extends AbstractController
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $feeds = $this->getDoctrine()
            ->getRepository('App:Feed')
            ->findBy(array(), array('id' => 'DESC'), 1);

        $feed = is_array($feeds) && isset($feeds[0]) ? $feeds[0] : array();

        return $this->render('controller/module/top_feed/top_feed.html.twig', [
            'id' => 'topFeed',
            'feed' => $feed
        ]);
    }
}
