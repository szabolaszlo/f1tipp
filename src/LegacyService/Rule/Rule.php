<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 23:07
 */

namespace App\LegacyService\Rule;

use App\LegacyService\Rule\RuleType\IRuleType;

/**
 * Class Rule
 * @package App\LegacyService\Rule
 */
class Rule
{
    protected $ruleTypes = array();

    /**
     * Rule constructor.
     * @param array $ruleTypes
     */
    public function __construct(array $ruleTypes)
    {
        $this->ruleTypes = $ruleTypes;
    }

    /**
     * @param $ruleType
     * @return IRuleType
     */
    public function getRuleType($ruleType)
    {
        /** @var IRuleType $rule */
        $rule = $this->ruleTypes[$ruleType];
        
        return $rule;
    }
}
