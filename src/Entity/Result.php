<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 20:50
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Result")
 * @ORM\Table(name="`result`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Result
{
    /**
     * @ORM\Column(name="id", type="integer", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event", referencedColumnName="id")
     */
    protected $event;

    /**
     * @ORM\OneToMany(targetEntity="ResultAttribute", mappedBy="result", cascade={"persist","remove"})
     */
    protected $attributes;

    /**
     * Result constructor.
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

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
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param ResultAttribute $attribute
     */
    public function addAttribute(ResultAttribute $attribute)
    {
        $this->attributes->add($attribute);
        $attribute->setResult($this);
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function getAttributeValueByKey($key)
    {
        /** @var ResultAttribute $attribute */
        foreach ($this->attributes as $attribute) {
            if ($attribute->getKey() == $key) {
                return $attribute->getValue();
            }
        }

        return false;
    }

    /**
     * @param $value
     * @return bool
     */
    public function existAttributeByValue($value)
    {
        /** @var ResultAttribute $attribute */
        foreach ($this->attributes as $attribute) {
            if ($attribute->getValue() == $value) {
                return true;
            }
        }

        return false;
    }
}
