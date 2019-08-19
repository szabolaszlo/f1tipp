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
 * @ORM\Entity
 * @ORM\Table(name="`bet`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Bet
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event_id;

    /**
     * @ORM\OneToMany(targetEntity="BetAttribute", mappedBy="bet", cascade={"persist","remove"})
     */
    protected $attributes;

    /**
     * @var int
     */
    protected $point = 0;

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
    public function getUser()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUser($user_id)
    {
        $this->user_id = $user_id;
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
        return $this->event_id;
    }

    /**
     * @param mixed $event_id
     */
    public function setEvent($event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $key
     * @return bool|BetAttribute
     */
    public function getAttributeValueByKey($key)
    {
        /** @var BetAttribute $attribute */
        foreach ($this->attributes as $attribute) {
            if ($attribute->getKey() == $key) {
                return $attribute->getValue();
            }
        }
        
        return false;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param BetAttribute $attribute
     */
    public function addAttribute(BetAttribute $attribute)
    {
        $this->attributes->add($attribute);
        $attribute->setBet($this);
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
}
