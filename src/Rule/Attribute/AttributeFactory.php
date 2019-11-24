<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:14
 */

namespace App\Rule\Attribute;

/**
 * Class AttributeFactory
 * @package App\Rule\AttributeFactory
 */
class AttributeFactory
{
    /**
     * @param array $array
     * @return array
     */
    public function createAttributes(array $array)
    {
        $attributes = [];

        foreach ($array as $attribute) {
            $attributes[$attribute[0]] = new Attribute(
                $attribute[0],
                $attribute[1],
                $attribute[2],
                $attribute[3]
            );
        }

        return $attributes;
    }
}
