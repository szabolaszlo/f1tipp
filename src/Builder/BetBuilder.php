<?php

namespace App\Builder;

use App\Entity\Bet;
use App\Entity\BetAttribute;
use App\Entity\Event;
use App\Rule\Attribute\Attribute;
use App\Rule\RuleRegistry;

/**
 * Class BetBuilder
 * @package App\Builder
 */
class BetBuilder
{
    /**
     * @var RuleRegistry
     */
    protected $ruleRegistry;

    /**
     * BetBuilder constructor.
     * @param RuleRegistry $ruleRegistry
     */
    public function __construct(RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    /**
     * @param Event $event
     * @return Bet
     */
    public function buildForEvent(Event $event)
    {
        $bet = new Bet();
        $bet->setEvent($event);

        $rule = $this->ruleRegistry->getRuleByType($event->getType());
        $ruleAttributes = $rule->getAllAttribute();

        /** @var Attribute $ruleAttribute */
        foreach ($ruleAttributes as $ruleAttribute) {
            $betAttribute = new BetAttribute();
            $betAttribute->setKey($ruleAttribute->getId());
            $bet->addAttribute($betAttribute);
        }

        return $bet;
    }
}