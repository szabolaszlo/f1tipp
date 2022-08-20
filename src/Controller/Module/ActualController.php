<?php

namespace App\Controller\Module;

use App\Calculator\Calculator;
use App\Entity\Event;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActualController extends AbstractController
{
    /**
     * @Route(path="/module/actual", name="module_actual", methods={"GET"})
     * @return Response
     * @throws Exception
     */
    public function indexAction(Calculator $calculator)
    {
        $data['faceCoverImage'] = $this->getDoctrine()
            ->getRepository('App:Setting')
            ->getValueByKey('faceCoverImage');

        $events = $this->getDoctrine()->getRepository('App:Event')->getActualWeekendEvents();

        $now = new \DateTime();
        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());
            $titleEvents[$id] = $event;
        }
        ksort($titleEvents);
        $titleEvent = reset($titleEvents);

        $data['titleEvent'] = array(
            'id' => 'title_' . $titleEvent->getType(),
            'name' => $titleEvent->getName(),
            'date' => $titleEvent->getDateTime()->format('M.d H:i'),
            'remain_time' => $now->diff($titleEvent->getDateTime())
        );

        return $this->render('controller/module/actual.html.twig', $data);
    }
}