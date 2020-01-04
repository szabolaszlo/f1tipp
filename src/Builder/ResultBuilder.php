<?php

namespace App\Builder;

use App\Entity\Result;
use App\Entity\ResultAttribute;
use App\Rule\Attribute\Attribute;
use App\Rule\RuleRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ResultBuilder
 * @package App\Builder
 */
class ResultBuilder
{
    /**
     * @var RuleRegistry
     */
    protected $ruleRegistry;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * ResultBuilder constructor.
     * @param RuleRegistry $ruleRegistry
     * @param EntityManagerInterface $em
     */
    public function __construct(RuleRegistry $ruleRegistry, EntityManagerInterface $em)
    {
        $this->ruleRegistry = $ruleRegistry;
        $this->em = $em;
    }

    /**
     * @return Result
     */
    public function build()
    {
        $result = new Result();
        $event = $this->em->getRepository('App:Event')->getNextEventForResult();
        $result->setEvent($event);
        $result->setIsCalculated(false);

        $rule = $this->ruleRegistry->getRuleByType($event->getType());
        $ruleAttributes = $rule->getAllAttribute();

        /** @var Attribute $ruleAttribute */
        foreach ($ruleAttributes as $ruleAttribute) {
            $resultAttribute = new ResultAttribute();
            $resultAttribute->setKey($ruleAttribute->getId());
            $result->addAttribute($resultAttribute);
        }

        return $result;
    }
}