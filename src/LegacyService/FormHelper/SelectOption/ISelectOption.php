<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 21.
 * Time: 13:40
 */
namespace App\LegacyService\FormHelper\SelectOption;

/**
 * Class Driver
 * @package App\LegacyService\FormHelper\SelectOption
 */
interface ISelectOption
{
    /**
     * @param null $selectedValue
     * @return mixed
     */
    public function getOptions($selectedValue = null);
}
