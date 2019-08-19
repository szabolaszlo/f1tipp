<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:14
 */

namespace App\LegacyService\Rule\Attribute;

/**
 * Class AttributeFactory
 * @package App\LegacyService\Rule\AttributeFactory
 */
class AttributeFactory
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @param array $array
     * @return array
     */
    public function createAttributes(array $array)
    {
        foreach ($array as $attribute) {
            $this->attributes[$attribute[0]] = new Attribute(
                $attribute[0],
                $attribute[1],
                $attribute[2],
                $attribute[3]
            );
        }
        
        return $this->attributes;
    }
}
