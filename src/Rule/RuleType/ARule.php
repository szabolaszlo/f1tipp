<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:41
 */

namespace App\Rule\RuleType;

use App\Rule\Attribute\Attribute;
use App\Rule\Attribute\AttributeFactory;

/**
 * Class ARule
 * @package App\Rule
 */
abstract class ARule implements IRule
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
     * @var string
     */
    protected $type = '';

    /**
     * ARule constructor.
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(AttributeFactory $attributeFactory)
    {
        $this->attributes = $attributeFactory->createAttributes($this->betAbleAttributes);
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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
