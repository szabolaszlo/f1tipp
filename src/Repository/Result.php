<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 28.
 * Time: 20:28
 */

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Race;
use Doctrine\ORM\EntityRepository;
use App\Entity\Result as ResultEnt;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Result
 * @package App\Repository
 */
class Result extends EntityRepository
{
    /**
     * @param string $type
     * @return array
     */
    public function findByType($type = '')
    {
        $results = parent::findAll();

        /**
         * @var  integer $key
         * @var  ResultEnt $result
         */
        foreach ($results as $key => $result) {
            $event = $result->getEvent();
            if ($event->getType() !== $type) {
                unset($results[$key]);
            }
        }

        return $results;
    }

    /**
     * @return mixed
     */
    public function getCalculatedRacesWithoutTrophies()
    {
        $result = $this->createQueryBuilder('result')
            ->select('race')
            ->innerJoin('App:Race', 'race', Join::WITH, 'result.event = race.id')
            ->where('result.isCalculated = 1')
            ->getQuery()
            ->getResult();

        /** @var Race $race */
        foreach ($result as $key => $race) {
            if (!$race->getTrophies()->isEmpty()) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getNotCalculatedEvents()
    {
        return $this->createQueryBuilder('result')
            ->select('event')
            ->innerJoin('App:Event', 'event', Join::WITH, 'result.event = event.id')
            ->where('result.isCalculated != 1')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Event $event
     * @return object
     */
    public function getResultByEvent(Event $event)
    {
        return $this->findOneBy(['event' => $event]);
    }
}
