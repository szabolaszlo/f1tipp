<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class Bet
 * @package App\Repository
 */
class Bet extends EntityRepository
{
    /**
     * @param $event
     * @return array
     */
    public function getBetsByEvent($event)
    {
        return $this->findBy(['event_id' => $event]);
    }
}
