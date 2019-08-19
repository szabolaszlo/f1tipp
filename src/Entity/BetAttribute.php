<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 21:07
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`bet_attribute`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class BetAttribute
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Bet", inversedBy="attributes")
     * @ORM\JoinColumn(name="bet_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $bet;

    /**
     * @var string
     * @ORM\Column(name="`key`", type="string", length=16, nullable=false)
     */
    protected $key;

    /**
     * @ORM\Column(name="`value`", type="string", length=4, nullable=false)
     */
    protected $value;

    /**
     * @var int
     */
    protected $point = 0;

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getBet()
    {
        return $this->bet;
    }

    /**
     * @param mixed $bet
     */
    public function setBet($bet)
    {
        $this->bet = $bet;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param int $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}
