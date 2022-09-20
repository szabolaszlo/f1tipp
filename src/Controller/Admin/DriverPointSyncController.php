<?php

namespace App\Controller\Admin;

use App\Controller\Module\ResultsOfChampionshipController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DriverPointSyncController
 * @package App\Controller\Admin
 */
class DriverPointSyncController extends AbstractController
{
    /**
     * @Route(path = "/admin/driver/point_sync", name = "driver_point_sync")
     * @Security("is_granted('ROLE_ADMIN')")
     * @param Request $request
     * @return RedirectResponse
     */
    public function driverPointSyncAction(Request $request)
    {
        $response = json_decode(file_get_contents(ResultsOfChampionshipController::DRIVER_JSON_PATH), true);

        $standings = $response['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];

        foreach ($standings as $standing) {
            $point = $standing['points'];
            $driverShort = $standing['Driver']['code'];

            $driverEntity = $this->getDoctrine()
                ->getRepository('App\Entity\Driver')
                ->findOneBy(array('short' => $driverShort));

            if ($driverEntity) {
                $driverEntity->setPoint($point);
                $this->getDoctrine()->getManager()->persist($driverEntity);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'admin_driver_editor_sync_success');

        // redirect to the 'list' view of the given entity ...
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $request->query->get('entity'),
        ));
    }
}
