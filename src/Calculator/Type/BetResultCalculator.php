<?php

namespace App\Calculator\Type;

use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Event;
use App\Entity\Result;
use App\Rule\Attribute\Attribute;
use App\Rule\RuleRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

/**
 * Class BetResultCalculator
 * @package App\Calculator\Type
 */
class BetResultCalculator extends ACalculator
{
    const HIGH_CLASS = 'bet-high';

    const LOW_CLASS = 'bet-low';

    /**
     * @var RuleRegistry
     */
    protected $ruleRegistry;

    /**
     * @var Event
     */
    protected $notCalculatedEvents;

    /**
     * BetResultCalculator constructor.
     * @param EntityManagerInterface $em
     * @param RuleRegistry $ruleRegistry
     */
    public function __construct(EntityManagerInterface $em, RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
        parent::__construct($em);
    }

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * @return boolean
     */
    public function isNeedCalculate()
    {
        $this->notCalculatedEvents = $this->em->getRepository("App:Result")->getNotCalculatedEvents();
        return (bool)(count($this->notCalculatedEvents));
    }

    /**
     * @throws ORMException
     */
    public function calculate()
    {
        foreach ($this->notCalculatedEvents as $event) {
            $bets = $this->em->getRepository('App:Bet')->getBetsByEvent($event);
            $result = $this->em->getRepository('App:Result')->getResultByEvent($event);

            /** @var Bet $bet */
            foreach ($bets as $bet) {
                if (!$bet->getPointSummary()) {
                    $this->calculateBetPoints($bet, $result);
                }
            }

            $result->setIsCalculated(true);
            $this->em->persist($result);
        }

        $this->em->flush();
        $resultCache = $this->em->getConfiguration()->getResultCacheImpl();
        $resultCache->delete('topRaceBets');
        $resultCache->delete('topQualifyBets');
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
