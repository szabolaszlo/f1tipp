<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 14.
 * Time: 19:12
 */

namespace App\Controller\Module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Qualify;
use App\Entity\Race;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CountDownController
 * @package App\Controller\Module\CountDown
 */
class CountDownController extends AbstractController
{
    /**
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        //TODO Move this to Lazy Twig Extension (every subrequest has 20ms cost)

        /** @var Qualify $qualify */
        $qualify = $this->getDoctrine()->getRepository('App:Qualify')->getNextEvent();

        /** @var Race $race */
        $race = $this->getDoctrine()->getRepository('App:Race')->getNextEvent();

        $now = new \DateTime();

        return $this->render("controller/module/count_down/count_down.html.twig",
            ['events' => [
                'qualify' => array(
                    'id' => $qualify->getType(),
                    'name' => $qualify->getName(),
                    'date' => $qualify->getDateTime()->format('M.d H:i'),
                    'remain_time' => $now->diff($qualify->getDateTime())
                ),
                'race' => array(
                    'id' => $race->getType(),
                    'name' => $race->getName(),
                    'date' => $race->getDateTime()->format('M.d H:i'),
                    'remain_time' => $now->diff($race->getDateTime())
                ),
            ],
                'details_link' => '/calendar',
                'id' => 'count_down',
            ]
        );
    }
}
