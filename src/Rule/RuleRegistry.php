<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 23:07
 */

namespace App\Rule;

use App\Rule\RuleType\IRule;

/**
 * Class Rule
 * @package App\Rule
 */
class RuleRegistry
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @param IRule $rule
     */
    public function addRule(IRule $rule)
    {
        $this->rules[$rule->getType()] = $rule;
    }

    /**
     * @param $type
     * @return IRule
     */
    public function getRuleByType($type)
    {
        /** @var IRule $rule */
        $rule = $this->rules[$type];

        return $rule;
    }
}
