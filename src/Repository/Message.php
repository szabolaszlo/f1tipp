<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

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
}
