<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 25.
 * Time: 11:06
 */

namespace App\Controller\Module\ResultsOfChampionship;

use App\Controller\Controller;

/**
 * Class ResultsOfChampionship
 * @package App\Controller\Module\ResultsOfChampionship
 */
class ResultsOfChampionship extends Controller
{
    const DRIVER_JSON_PATH = "http://ergast.com/api/f1/current/driverStandings.json";

    const CONSTRUCT_JSON_PATH = "http://ergast.com/api/f1/current/constructorStandings.json";

    public function indexAction()
    {
        return $this->registry->getCache()->getCache($this->id)
            ? $this->registry->getCache()->getCache($this->id)
            : $this->getResultsAction();
    }

    /**
     * @return string
     */
    public function getResultsAction()
    {
        $driverResponse = json_decode(file_get_contents(self::DRIVER_JSON_PATH), true);

        $constructResponse = json_decode(file_get_contents(self::CONSTRUCT_JSON_PATH), true);

        $eventOrder = $driverResponse['MRData']['StandingsTable']['StandingsLists'][0]['round'];

        $event = $this->entityManager
            ->getRepository('Entity\Race')
            ->findOneBy(array('eventOrder' => $eventOrder));

        $this->data['event'] = $event;

        $this->data['driverStandings'] =
            $driverResponse['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];

        $this->data['constructStandings'] =
            $constructResponse['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'];

        $this->setTemplate('controller/module/results_of_championship/results_of_championship.tpl');

        $this->registry->getCache()->setCache($this->id, $this->render());

        return $this->registry->getCache()->getCache($this->id);
    }
}
