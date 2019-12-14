<?php

namespace App\Repository;

use App\Entity\User;
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

    /**
     * @param $user
     * @return array
     */
    public function getBetsByUser($user)
    {
        return $this->findBy(['user_id' => $user]);
    }

    /**
     * @param User $user
     * @param array $events
     * @return array
     */
    public function getBetByUserAndEvents(User $user, array $events)
    {
        return $this->findBy([
            'user_id' => $user,
            'event_id' => $events
        ]);
    }
}
