<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:41
 */

namespace App\LegacyService\Rule\RuleType;

use App\LegacyService\Rule\Attribute\Attribute;
use App\LegacyService\Rule\Attribute\AttributeFactory;

/**
 * Class ARule
 * @package App\LegacyService\Rule
 */
abstract class ARuleType implements IRuleType
{
    /**
     * @var array(id, type, highPoint, lowPoint)
     */
    protected $betAbleAttributes = array();

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * ARule constructor.
     */
    public function __construct()
    {
        $this->attributeFactory = new AttributeFactory();

        $this->attributes = $this->attributeFactory->createAttributes($this->betAbleAttributes);
    }

    /**
     * @param $attributeId
     * @return Attribute
     */
    public function getAttributeById($attributeId)
    {
        /** @var Attribute $attribute */
        $attribute = $this->attributes[$attributeId];
        return $attribute;
    }

    /**
     * @return array
     */
    public function getAllAttribute()
    {
        return $this->attributes;
    }
}
