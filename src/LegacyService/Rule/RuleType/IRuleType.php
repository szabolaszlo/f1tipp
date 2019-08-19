<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:44
 */
namespace App\LegacyService\Rule\RuleType;

use App\LegacyService\Rule\Attribute\Attribute;

/**
 * Class Qualify
 * @package App\LegacyService\Rule
 */
interface IRuleType
{
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
