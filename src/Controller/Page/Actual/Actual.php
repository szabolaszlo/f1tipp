<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 23:30
 */

namespace Controller\Page\Actual;

use Controller\Controller;
use Entity\Event;
use Entity\Result;
use Entity\Setting;
use System\CountDownScript\CountDownScript;

/**
 * Class Actual
 * @package Controller\Page\Actual
 */
class Actual extends Controller
{
    const ACTUAL_IMAGE_RELATIVE_URL = '/src/view/image/aktual.jpg';

    /**
     * @return mixed
     */
    public function indexAction()
    {
        $this->data['faceCoverImage'] = $this->entityManager
            ->getRepository(Setting::class)
            ->getValueByKey('faceCoverImage');

        if (!$this->data['faceCoverImage']) {
            $this->data['image'] = self::ACTUAL_IMAGE_RELATIVE_URL;
            $this->data['imageModifyTime'] = filemtime(
                $this->registry->getServer()->getDocumentRoot() . $this->data['image']
            );
        }

        $events = array(
            $this->entityManager->getRepository('Entity\Qualify')->getNextEvent(),
            $this->entityManager->getRepository('Entity\Race')->getNextEvent()
        );

        $user = $this->registry->getUserAuth()->getLoggedUser();

        $this->data['tables'] = array();

        $now = new \DateTime();

        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());

            $titleEvents[$id] = $event;

            $this->data['tables'][$id] = $this->registry->getResultTable()->getTable($user, $event);
        }

        ksort($this->data['tables']);

        ksort($titleEvents);

        $titleEvent = reset($titleEvents);

        $countDownTitleEvent = new CountDownScript('title_' . $titleEvent->getType(), $titleEvent->getDateTime(), $this->renderer);

        $this->data['titleEvent'] = array(
                'id' => 'title_' . $titleEvent->getType(),
                'name' => $titleEvent->getName(),
                'date' => $titleEvent->getDateTime()->format('M.d H:i'),
                'countDown' => $countDownTitleEvent->render()
        );

        return $this->render();
    }
}
