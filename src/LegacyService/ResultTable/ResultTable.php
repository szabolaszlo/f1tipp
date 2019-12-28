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
use App\LegacyService\ResultTable\Type\ITableType;

/**
 * Class ResultTable
 * @package App\LegacyService\ResultTable
 */
class ResultTable
{

    /**
     * @var ResultTableRegistry
     */
    protected $tableRegistry;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * ResultTable constructor.
     * @param ResultTableRegistry $tableRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(ResultTableRegistry $tableRegistry, EntityManagerInterface $em)
    {
        $this->tableRegistry = $tableRegistry;
        $this->em = $em;
    }

    /**
     * @param $user
     * @param Event $event
     * @param null $type
     * @return ITableType
     * @throws \Exception
     */
    public function getTable($user, Event $event, $type = null)
    {
        if ($type === null) {

            $type = 'only_users';

            $result = $this->em->getRepository('App:Result')->getResultByEvent($event);

            $userBet = $user
                ? $this->em->getRepository('App:Bet')->getBetByUserAndEvent($user, $event)
                : false;

            if ($userBet) {
                $type = 'only_bets';
            }

            if ($result) {
                $type = 'full';
            }
        }

        return $this->tableRegistry->getTableByType($type);
    }
}