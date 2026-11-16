<?php

namespace App\Display;

use App\Rule\Attribute\Attribute;
use App\Rule\RuleRegistry;

/**
 * A tipptáblák jelmagyarázatához szükséges pontértékek az eseménytípus szabályából.
 */
class BetLegendProvider
{
    private RuleRegistry $ruleRegistry;

    public function __construct(RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    /**
     * @return array{exact: int, near: int, question: ?int, positions: int}
     */
    public function forEventType(string $type): array
    {
        $legend = ['exact' => 0, 'near' => 0, 'question' => null, 'positions' => 0];
        $driverSeen = false;

        /** @var Attribute $attribute */
        foreach ($this->ruleRegistry->getRuleByType($type)->getAllAttribute() as $attribute) {
            if ($attribute->getType() === 'driver') {
                if (!$driverSeen) {
                    $legend['exact'] = (int)$attribute->getHighPoint();
                    $legend['near'] = (int)$attribute->getLowPoint();
                    $driverSeen = true;
                }
                $legend['positions']++;
            } elseif ($attribute->getType() === 'question' && $legend['question'] === null) {
                $legend['question'] = (int)$attribute->getHighPoint();
            }
        }

        return $legend;
    }
}
