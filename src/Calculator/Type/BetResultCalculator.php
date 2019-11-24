<?php

namespace App\Calculator\Type;

use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Event;
use App\Entity\Result;
use App\Rule\Attribute\Attribute;
use App\Rule\RuleRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

/**
 * Class BetResultCalculator
 * @package App\Calculator\Type
 */
class BetResultCalculator implements ICalculator
{
    const HIGH_CLASS = 'bet-high';

    const LOW_CLASS = 'bet-low';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var RuleRegistry
     */
    protected $ruleRegistry;

    /**
     * @var Event
     */
    protected $firstNotCalculatedEvent;

    /**
     * BetResultCalculator constructor.
     * @param EntityManager $em
     * @param RuleRegistry $ruleRegistry
     */
    public function __construct(EntityManager $em, RuleRegistry $ruleRegistry)
    {
        $this->em = $em;
        $this->ruleRegistry = $ruleRegistry;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'bet_result';
    }

    /**
     * @param Event $firstNotCalculatedEvent
     * @return boolean
     */
    public function isNeedCalculate(Event $firstNotCalculatedEvent)
    {
        $this->firstNotCalculatedEvent = $firstNotCalculatedEvent;
        return (bool)($this->firstNotCalculatedEvent->getId());
    }

    /**
     * @throws ORMException
     */
    public function calculate()
    {
        while ($this->firstNotCalculatedEvent->getId()) {
            $bets = $this->em->getRepository('App:Bet')->getBetsByEvent($this->firstNotCalculatedEvent);
            $result = $this->em->getRepository('App:Result')->getResultByEvent($this->firstNotCalculatedEvent);

            /** @var Bet $bet */
            foreach ($bets as $bet) {
                if (!$bet->getPointSummary()) {
                    $this->calculateBetPoints($bet, $result);
                }
            }

            $result->setIsCalculated(true);
            $this->em->persist($result);
            $this->em->flush();

            $this->firstNotCalculatedEvent = $this->em->getRepository('App:Result')->getFirstNotCalculatedEvent();
        }
    }

    /**
     * @param Bet $bet
     * @param Result $result
     * @throws ORMException
     */
    protected function calculateBetPoints(Bet $bet, Result $result)
    {
        $betFullPoints = 0;

        $rule = $this->ruleRegistry->getRuleByType($result->getEvent()->getType());

        /** @var BetAttribute $betAttribute */
        foreach ($bet->getAttributes() as $betAttribute) {
            $ruleAttribute = $rule->getAttributeById($betAttribute->getKey());

            $betFullPoints += $this->calculateHighPoint($betAttribute, $result, $ruleAttribute);
            $betFullPoints += $this->calculateLowPoint($betAttribute, $result, $ruleAttribute);
        }

        $bet->setPointSummary($betFullPoints);

        $this->em->persist($bet);
    }

    /**
     * @param BetAttribute $betAttribute
     * @param Result $result
     * @param Attribute $ruleAttribute
     * @return int
     * @throws ORMException
     */
    protected function calculateHighPoint(BetAttribute $betAttribute, Result $result, Attribute $ruleAttribute)
    {
        if ($betAttribute->getValue() == $result->getAttributeValueByKey($betAttribute->getKey())) {
            $betAttribute->setPoint($ruleAttribute->getHighPoint());
            $betAttribute->setClass(self::HIGH_CLASS);
            $this->em->persist($betAttribute);
            return $ruleAttribute->getHighPoint();
        }

        return 0;
    }

    /**
     * @param BetAttribute $betAttribute
     * @param Result $result
     * @param Attribute $ruleAttribute
     * @return int
     * @throws ORMException
     */
    protected function calculateLowPoint(BetAttribute $betAttribute, Result $result, Attribute $ruleAttribute)
    {
        if ($ruleAttribute->getLowPoint()
            && !$betAttribute->getPoint()
            && $result->existAttributeByValue($betAttribute->getValue())
        ) {
            $betAttribute->setPoint($ruleAttribute->getLowPoint());
            $betAttribute->setClass(self::LOW_CLASS);
            $this->em->persist($betAttribute);
            return $ruleAttribute->getLowPoint();
        }

        return 0;
    }
}
