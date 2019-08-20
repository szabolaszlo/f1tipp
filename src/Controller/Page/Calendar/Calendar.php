<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 19.
 * Time: 22:10
 */

namespace App\Controller\Page\Calendar;

use App\Controller\Controller;
use Entity\Repository\Event;

/**
 * Class Calendar
 * @package App\Controller\Page\Calendar
 */
class Calendar extends Controller
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('Entity\Event');

        $this->data['events'] = $repository->getRemainEvents();

        $this->data['type'] = 'Event';

        return $this->render();
    }

    /**
     * @return string
     */
    public function qualifyAction()
    {
        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('Entity\Qualify');

        $this->data['events'] = $repository->getRemainEvents();

        $this->data['type'] = 'Qualify';

        return $this->render();
    }

    /**
     * @return string
     */
    public function raceAction()
    {
        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('Entity\Race');

        $this->data['events'] = $repository->getRemainEvents();

        $this->data['type'] = 'Race';

        return $this->render();
    }
}
