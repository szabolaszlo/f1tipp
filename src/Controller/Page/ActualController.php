<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 23:30
 */

namespace App\Controller\Page;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ActualController
 * @package App\Controller\Page
 */
class ActualController extends AbstractController
{
    /**
     * @Route(path="/", name="home", methods={"GET"})
     * @param ManagerRegistry $managerRegistry
     * @return Response
     */
    public function indexAction(ManagerRegistry $managerRegistry): Response
    {
        $ga4MeasurementId = $managerRegistry
            ->getRepository('App:Setting')
            ->getValueByKey('ga4MeasurementId');

        return $this->render('controller/page/actual.html.twig',
            ['ga4_measurement_id' => $ga4MeasurementId]
        );
    }
}
