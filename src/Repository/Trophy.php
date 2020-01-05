<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;

class Trophy extends EntityRepository
{
    /**
     * @param $events
     * @return Event
     */
    public function removeTrophiesByEvents($events)
    {
        $eventIds = [];
        foreach ($events as $event) {
            $eventIds[] = $event->getId();
        }

        return $this->createQueryBuilder('trophy')
            ->delete('App:Trophy', 't')
            ->where('t.event IN (:e)')
            ->setParameter('e', $eventIds)
            ->getQuery()
            ->execute();
    }
}
