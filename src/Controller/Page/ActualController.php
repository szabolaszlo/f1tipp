<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 23:30
 */

namespace App\Controller\Page;

use App\Calculator\Calculator;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Event;
use App\Entity\Result;
use App\Entity\Setting;

//use System\CountDownScript\CountDownScript;

/**
 * Class ActualController
 * @package App\Controller\Page
 */
class ActualController extends AbstractController
{
    const ACTUAL_IMAGE_RELATIVE_URL = '/src/view/image/aktual.jpg';

    /**
     * @Route(path="/", name="home", methods={"GET"})
     * @return Response
     * @throws Exception
     */
    public function indexAction(Calculator $calculator)
    {
        $data['faceCoverImage'] = $this->getDoctrine()
            ->getRepository('App:Setting')
            ->getValueByKey('faceCoverImage');

        if (!$data['faceCoverImage']) {
            $data['image'] = self::ACTUAL_IMAGE_RELATIVE_URL;
        }

        $events = array(
            $this->getDoctrine()->getRepository('App\Entity\Qualify')->getNextEvent(),
            $this->getDoctrine()->getRepository('App\Entity\Race')->getNextEvent()
        );

        $calculator->calculate();
        $user = $this->getUser();

        $data['tables'] = array();

        $now = new \DateTime();

        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());

            $titleEvents[$id] = $event;
//
//            $data['tables'][$id] = $this->registry->getResultTable()->getTable($user, $event);
        }

//        ksort($data['tables']);
//
        ksort($titleEvents);
//
        $titleEvent = reset($titleEvents);

        $data['titleEvent'] = array(
            'id' => 'title_' . $titleEvent->getType(),
            'name' => $titleEvent->getName(),
            'date' => $titleEvent->getDateTime()->format('M.d H:i'),
            'remain_time' => $now->diff($titleEvent->getDateTime())
        );

        return $this->render('controller/page/actual.html.twig', $data);
    }
}
