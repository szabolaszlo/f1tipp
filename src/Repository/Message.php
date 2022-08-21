<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class Message
 * @package App\Repository
 */
class Message extends EntityRepository
{
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->findBy([], ['id' => 'DESC'], 100);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getMessageCount()
    {
        return $this->createQueryBuilder('message')
                ->select('count(message.id)')
                ->getQuery()
                ->getSingleScalarResult() ?? 0;
    }
}
