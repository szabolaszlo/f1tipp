<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 14.
 * Time: 19:12
 */

namespace App\Controller\Module\CountDown;

use App\Controller\Controller;
use Entity\Qualify;
use Entity\Race;
use Entity\Repository\Event;
use System\CountDownScript\CountDownScript;

/**
 * Class CountDownScript
 * @package App\Controller\Module\CountDownScript
 */
class CountDown extends Controller
{
    /**
     * @return mixed
     */
    public function indexAction()
    {
        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('Entity\Qualify');

        /** @var Qualify $qualify */
        $qualify = $repository->getNextEvent();

        $countDownQualify = new CountDownScript($qualify->getType(), $qualify->getDateTime(), $this->renderer);

        /** @var Event $repository */
        $repository = $this->entityManager->getRepository('Entity\Race');

        /** @var Race $race */
        $race = $repository->getNextEvent();

        $countDownRace = new CountDownScript($race->getType(), $race->getDateTime(), $this->renderer);

        $this->data['events'] = array(
            'qualify' => array(
                'id' => $qualify->getType(),
                'name' => $qualify->getName(),
                'date' => $qualify->getDateTime()->format('M.d H:i'),
                'countDown' => $countDownQualify->render()
            ),
            'race' => array(
                'id' => $race->getType(),
                'name' => $race->getName(),
                'date' => $race->getDateTime()->format('M.d H:i'),
                'countDown' => $countDownRace->render()
            ),
        );

        $this->data['detailsLink'] = '/?page=calendar/index';
        
        return $this->render();
    }
}
