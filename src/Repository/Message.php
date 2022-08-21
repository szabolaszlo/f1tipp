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
     * @return int
     */
    public function getMessageCount(): int
    {
        return count(
            $this->createQueryBuilder('message')
                ->setCacheable(true)
                ->select()
                ->getQuery()
                ->getResult() ?? []
        );
    }
}
