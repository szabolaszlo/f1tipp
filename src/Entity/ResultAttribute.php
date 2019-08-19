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
 * @ORM\Table(name="`result_attribute`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ResultAttribute
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Result", inversedBy="attributes")
     * @ORM\JoinColumn(name="result_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $result;

    /**
     * @ORM\Column(name="`key`", type="string", length=16, nullable=false)
     */
    protected $key;

    /**
     * @ORM\Column(name="`value`", type="string", length=4, nullable=false)
     */
    protected $value;

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
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
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
}
