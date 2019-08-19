<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 27.
 * Time: 13:51
 */

namespace App\LegacyService\Calculator;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Event;
use App\Entity\Race;
use App\Entity\Result;
use App\Entity\User;
use App\LegacyService\Calculator\RecordCollector\RecordCollector;
use App\LegacyService\Registry\IRegistry;
use App\LegacyService\Rule\Attribute\Attribute;
use App\LegacyService\Rule\RuleType\IRuleType;

/**
 * Class Calculator
 * @package App\LegacyService\Calculator
 */
class Calculator implements ICalculator
{
    /**
     * @var IRegistry
     */
    protected $registry;

    /**
     * @var IRuleType
     */
    protected $rule;

    /**
     * @var int
     */
    protected $betFullPoints = 0;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RecordCollector
     */
    protected $recordCollector;

    /**
     * Calculator constructor.
     * @param IRegistry $registry
     */
    public function __construct(IRegistry $registry)
    {
        $this->registry = $registry;
        $this->entityManager = $this->registry->getEntityManager();
        $this->recordCollector = new RecordCollector();
    }

    /**
     * @param Bet $bet
     * @param Result $result
     */
    public function calculateBetPoints(Bet $bet, Result $result)
    {
        $this->betFullPoints = 0;

        /** @var IRuleType $rule */
        $this->rule = $this->registry->getRule()->getRuleType($result->getEvent()->getType());

        /** @var BetAttribute $betAttribute */
        foreach ($bet->getAttributes() as $betAttribute) {
            $ruleAttribute = $this->rule->getAttributeById($betAttribute->getKey());

            $this->calculateHighPoint($betAttribute, $result, $ruleAttribute);
            $this->calculateLowPoint($betAttribute, $result, $ruleAttribute);
        }

        $bet->setPoint($this->betFullPoints);
    }

    /**
     * @param BetAttribute $betAttribute
     * @param Result $result
     * @param Attribute $ruleAttribute
     */
    protected function calculateHighPoint(BetAttribute $betAttribute, Result $result, Attribute $ruleAttribute)
    {
        if ($betAttribute->getValue() == $result->getAttributeValueByKey($betAttribute->getKey())) {
            $betAttribute->setPoint($ruleAttribute->getHighPoint());
            $this->betFullPoints += $ruleAttribute->getHighPoint();
        }
    }

    /**
     * @param BetAttribute $betAttribute
     * @param Result $result
     * @param Attribute $ruleAttribute
     */
    protected function calculateLowPoint(BetAttribute $betAttribute, Result $result, Attribute $ruleAttribute)
    {
        if ($ruleAttribute->getLowPoint()
            && !$betAttribute->getPoint()
            && $result->existAttributeByValue($betAttribute->getValue())
        ) {
            $betAttribute->setPoint($ruleAttribute->getLowPoint());
            $this->betFullPoints += $ruleAttribute->getLowPoint();
        }
    }

    /**
     * @param User $user
     */
    public function calculateUserPoints(User $user)
    {
        $userPoints = 0;

        $results = $this->entityManager->getRepository('App\Entity\Result')->findAll();

        /** @var Result $result */
        foreach ($results as $result) {
            $bets = $this->entityManager
                ->getRepository('App\Entity\Bet')
                ->findBy(
                    array(
                        'event_id' => $result->getEvent(),
                        'user_id' => $user
                    )
                );

            /** @var Bet $bet */
            foreach ($bets as $bet) {
                if (!$bet->getPoint()) {
                    $this->calculateBetPoints($bet, $result);
                }
                $this->recordCollector->addRecord($bet, $result);
                $userPoints += $bet->getPoint();
            }
        }

        $user->setPoint($userPoints);
    }

    /**
     * @param Race $race
     * @return array
     */
    public function calculateUserPointsByCompleteWeekend(Race $race)
    {
        $qualify = $this->entityManager->getRepository('App\Entity\Qualify')
            ->findOneBy(array('eventOrder' => $race->getEventOrder()));

        $results = array(
            $this->entityManager->getRepository('App\Entity\Result')->findOneBy(array('event' => $qualify)),
            $this->entityManager->getRepository('App\Entity\Result')->findOneBy(array('event' => $race))
        );

        $users = $this->entityManager->getRepository('App\Entity\User')->findAll();

        /** @var User $user */
        foreach ($users as $user) {
            $userPoints = 0;

            foreach ($results as $result) {
                $bets = $this->entityManager
                    ->getRepository('App\Entity\Bet')
                    ->findBy(
                        array(
                            'event_id' => $result->getEvent(),
                            'user_id' => $user
                        )
                    );

                /** @var Bet $bet */
                foreach ($bets as $bet) {
                    if (!$bet->getPoint()) {
                        $this->calculateBetPoints($bet, $result);
                    }
                    $userPoints += $bet->getPoint();
                }
            }

            $user->setPoint($userPoints);
        }

        return $users;
    }

    /**
     * @param $type
     * @return array|mixed
     */
    public function getRecordsByType($type)
    {
        return $this->recordCollector->getRecordsByType($type);
    }
}
