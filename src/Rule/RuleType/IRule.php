<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:44
 */
namespace App\Rule\RuleType;

use App\Rule\Attribute\Attribute;

/**
 * Class Qualify
 * @package App\Rule
 */
interface IRule
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param $attributeId
     * @return Attribute
     */
    public function getAttributeById($attributeId);

    /**
     * @return array
     */
    public function getAllAttribute();
}
