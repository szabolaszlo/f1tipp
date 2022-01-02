<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 14.
 * Time: 19:12
 */

namespace App\Controller\Module;

use App\Entity\Event;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CountDownController
 * @package App\Controller\Module\CountDown
 */
class CountDownController extends AbstractController
{
    /**
     * @return Response
     * @throws Exception
     */
    public function indexAction(): Response
    {
        //TODO Move this to Lazy Twig Extension (every subrequest has 20ms cost)

        $events = $this->getDoctrine()->getRepository('App:Event')->getActualWeekendEvents();

        $data = [];

        $now = new DateTimeImmutable();

        /** @var Event $event */
        foreach ($events as $event) {
            $data[$event->getType()] = [
                'id' => $event->getType(),
                'name' => $event->getName(),
                'date' => $event->getDateTime()->format('M.d H:i'),
                'remain_time' => $now->diff($event->getDateTime())
            ];
        }

        return $this->render("controller/module/count_down/count_down.html.twig",
            [
                'events' => $data,
                'details_link' => '/calendar',
                'id' => 'count_down',
            ]
        );
    }
}
