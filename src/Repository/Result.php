<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 03. 28.
 * Time: 20:28
 */

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use App\Entity\Result as ResultEnt;

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
     * @return Event
     */
    public function getFirstNotCalculatedEvent()
    {
        $firstNotCalculatedResult = $this->findOneBy(
            ['isCalculated' => false],
            ['event' => 'asc']
        );

        if ($firstNotCalculatedResult instanceof ResultEnt) {
            return $firstNotCalculatedResult->getEvent();
        }

        return new Event();
    }

    /**
     * @param Event $event
     * @return ResultEnt|null
     */
    public function getResultByEvent(Event $event)
    {
        return $this->findOneBy(['event' => $event]);
    }
}
