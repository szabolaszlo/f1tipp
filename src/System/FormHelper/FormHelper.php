<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 21.
 * Time: 13:36
 */

namespace System\FormHelper;

use System\FormHelper\SelectOption\ISelectOption;

/**
 * Class FormHelper
 * @package System\FormHelper
 */
class FormHelper
{
    protected $selectOptions = array();

    /**
     * SelectOption constructor.
     * @param array $selectOptions
     */
    public function __construct(array $selectOptions)
    {
        $this->selectOptions = $selectOptions;
    }

    /**
     * @param $type
     * @return ISelectOption
     */
    public function getSelectOption($type)
    {
        /** @var ISelectOption $selectOption */
        $selectOption = $this->selectOptions[$type];

        return $selectOption;
    }
}
