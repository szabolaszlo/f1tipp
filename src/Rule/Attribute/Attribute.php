<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 20.
 * Time: 22:05
 */

namespace App\Rule\Attribute;

/**
 * Class Attribute
 * @package App\Rule\Attribute
 */
class Attribute
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $highPoint;

    /**
     * @var integer
     */
    protected $lowPoint;

    /**
     * Attribute constructor.
     * @param string $id
     * @param string $type
     * @param int $highPoint
     * @param int $lowPoint
     */
    public function __construct($id, $type, $highPoint, $lowPoint)
    {
        $this->id = $id;
        $this->type = $type;
        $this->highPoint = $highPoint;
        $this->lowPoint = $lowPoint;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getHighPoint()
    {
        return $this->highPoint;
    }

    /**
     * @return int
     */
    public function getLowPoint()
    {
        return $this->lowPoint;
    }
}
