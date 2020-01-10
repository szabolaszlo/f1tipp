<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueBetAttributes
 * @package App\Form\Constraint
 */
class UniqueBetAttributes extends Constraint
{
    /**
     * @var string
     */
    public $message = 'error_betting_form';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
