<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Race;
use App\Entity\Trophy as TrophyEnt;
use Doctrine\ORM\EntityRepository;

class Trophy extends EntityRepository
{
    /**
     * @return Event
     */
    public function getLastCalculatedTrophyEvent()
    {
        $lastCalculatedTrophyEvent = $this->findOneBy(
            ['event' => 'desc']
        );

        if ($lastCalculatedTrophyEvent instanceof TrophyEnt) {
            return $lastCalculatedTrophyEvent->getEvent();
        }

        return new Race();
    }
}
