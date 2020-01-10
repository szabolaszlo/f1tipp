<?php

namespace App\Form\Constraint;

use App\Entity\BetAttribute;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueBetAttributesValidator
 * @package App\Form\Constraint
 */
class UniqueBetAttributesValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof ArrayCollection) {
            $bet = [];
            /** @var BetAttribute $betAttribute */
            foreach ($value as $betAttribute) {
                if ($betAttribute->getValue() == 'empty') {
                    break;
                }
                $bet[] = $betAttribute->getValue();
            }
            $bet = array_unique($bet);
            if (count($bet) === count($value)) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}