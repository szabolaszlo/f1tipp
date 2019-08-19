<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 29.
 * Time: 15:55
 */

namespace App\LegacyService\ResultTable;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event;
use App\LegacyService\Registry\IRegistry;
use App\LegacyService\ResultTable\Type\ITableType;

/**
 * Class ResultTable
 * @package App\LegacyService\ResultTable
 */
class ResultTable
{
    /**
     * @var array
     */
    protected $tableTypes = array();

    /**
     * @var IRegistry
     */
    protected $registry;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ResultTable constructor.
     * @param IRegistry $registry
     * @param array $tableTypes
     */
    public function __construct(IRegistry $registry, array $tableTypes)
    {
        $this->registry = $registry;
        $this->entityManager = $this->registry->getEntityManager();
        $this->tableTypes = $tableTypes;
    }

    /**
     * @param $type
     * @param Event $event
     * @return string
     */
    public function getTableByType($type, Event $event)
    {
        /** @var ITableType $tableType */
        $tableType = $this->tableTypes[$type];

        return $tableType->getTable($event);
    }

    /**
     * @param $user
     * @param Event $event
     * @return string
     */
    public function getTable($user, Event $event)
    {
        $type = 'only_users';

        $result = $this->entityManager
            ->getRepository('App\Entity\Result')
            ->findOneBy(array('event' => $event));

        $userBet = $user
            ? $this->entityManager
                ->getRepository('App\Entity\Bet')
                ->findOneBy(array('user_id' => $user, 'event_id' => $event))
            : false;

        if ($userBet) {
            $type = 'only_bets';
        }

        if ($result) {
            $type = 'full';
        }

        return $this->getTableByType($type, $event);
    }
}
