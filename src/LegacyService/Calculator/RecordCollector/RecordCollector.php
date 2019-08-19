<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 30.
 * Time: 10:08
 */

namespace App\LegacyService\Calculator\RecordCollector;

use App\Entity\Bet;
use App\Entity\Result;
use App\LegacyService\Calculator\RecordCollector\Record\Record;

/**
 * Class RecordCollector
 * @package App\LegacyService\Calculator\RecordCollector
 */
class RecordCollector
{
    /**
     * @var array
     */
    protected $records = array();

    /**
     * @param Bet $bet
     * @param Result $result
     */
    public function addRecord(Bet $bet, Result $result)
    {
        $type = $result->getEvent()->getType();
        $userName = $bet->getUser()->getName();
        $userId = $bet->getUser()->getId();
        $point = $bet->getPoint();

        if (!isset($this->records[$type]) || empty($this->records[$type])) {
            $this->records[$type][$userId] = new Record($userName, $point);
            return;
        }

        /** @var Record $record */
        foreach ($this->records[$type] as $record) {
            if ($record->getPoint() < $point) {
                $record->setPoint($point);
                $record->setUserName($userName);
                $record->setTimes(1);
                $this->records[$type] = array($userId => $record);
                continue;
            }
            if ($record->getPoint() == $point && $userName != $record->getUserName()) {
                $new = new Record($userName, $point);
                $this->records[$type][$userId] = $new;
                continue;
            }
            if ($record->getPoint() == $point && $userName == $record->getUserName()) {
                $record->addTimes();
                $this->records[$type][$userId] = $record;
                continue;
            }
        }
    }

    /**
     * @param $type
     * @return array|mixed
     */
    public function getRecordsByType($type)
    {
        return isset($this->records[$type]) ? $this->records[$type] : array();
    }
}
