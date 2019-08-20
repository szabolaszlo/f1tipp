<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 13.
 * Time: 18:55
 */

namespace App\Controller\Page\Admin;

use App\Controller\Controller;
use Entity\Driver;

/**
 * Class DriverEditor
 * @package App\Controller\Page\Admin
 */
class DriverEditor extends Controller
{
    const DRIVER_JSON_PATH = "http://ergast.com/api/f1/current/driverStandings.json";

    /**
     * @return mixed
     */
    public function indexAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $this->data['drivers'] = $this->entityManager
            ->getRepository('Entity\Driver')
            ->findBy(array(), array('point' => 'DESC'));

        $this->data['enabledDrivers'] = count($this->entityManager
            ->getRepository('Entity\Driver')
            ->findBy(array('status' => true)));

        $this->data['success'] = $this->session->get('success');
        $this->session->remove('success');

        return $this->render();
    }

    /**
     * @return string
     */
    public function updateAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        if ($this->request->isPost()) {
            $drivers = $this->request->getPost('driver', array());

            foreach ($drivers as $driver) {
                $driverId = (int)$driver['id'];
                $driverEntity = $this->entityManager->getRepository('Entity\Driver')->find($driverId);

                if (!$driverEntity) {
                    $driverEntity = new Driver();
                }

                $driverEntity->setName($driver['name']);
                $driverEntity->setShort($driver['short']);
                $driverEntity->setPoint($driver['point']);
                $driverEntity->setStatus(isset($driver['status']) ? true : false);
                $this->entityManager->persist($driverEntity);
            }

            $this->entityManager->flush();

            $this->session->set('success', $this->registry->getLanguage()->get('admin_information_editor_success'));
            $this->registry->getServer()->redirect('page=admin/driver_editor/index');
        }

        $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
        return $this->render();
    }

    /**
     * @return string
     */
    public function insertAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $this->data['drivers'] = array(new Driver());
        
        return $this->render();
    }

    /**
     * @return string
     */
    public function syncPointsAction()
    {
        if (!$this->registry->getUserAuth()->isAdmin()) {
            $this->data['error'] = $this->registry->getLanguage()->get('admin_no_permisson_or_data_error');
            return $this->render();
        }

        $response = json_decode(file_get_contents(self::DRIVER_JSON_PATH), true);

        $standings = $response['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];

        foreach ($standings as $standing) {
            $point = $standing['points'];
            $driverShort = $standing['Driver']['code'];

            $driverEntity = $this->entityManager
                ->getRepository('Entity\Driver')
                ->findOneBy(array('short' => $driverShort));

            if ($driverEntity) {
                $driverEntity->setPoint($point);
                $this->entityManager->persist($driverEntity);
            }
        }

        $this->entityManager->flush();

        $this->session->set('success', $this->registry->getLanguage()->get('admin_driver_editor_sync_success'));
        $this->registry->getServer()->redirect('page=admin/driver_editor/index');
    }
}
