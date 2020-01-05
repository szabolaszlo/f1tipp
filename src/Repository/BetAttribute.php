<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class BetAttribute
 * @package App\Repository
 */
class BetAttribute extends EntityRepository
{
    /**
     * @param Event $event
     * @return mixed
     */
    public function clearBetAttributePointsByEvent(Event $event)
    {
        $result = $this->createQueryBuilder('betAttribute')
            ->select('betAttribute.id')
            ->innerJoin('App:Bet', 'b', Join::WITH, 'betAttribute.bet = b.id')
            ->where('b.event_id = :e')
            ->setParameter('e', $event->getId())
            ->getQuery()
            ->getArrayResult();
        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['id'] ?? 0;
        }

        if (!empty($ids)) {
            $this->createQueryBuilder('betAttribute')
                ->update('App:BetAttribute', 'b')
                ->set('b.point', 'NULL')
                ->set('b.class', 'NULL')
                ->where('b.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->execute();
        }
    }

}