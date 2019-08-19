<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2017. 01. 14.
 * Time: 22:06
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`trophy`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Trophy
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="trophies")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Race", inversedBy="trophies")
     * @ORM\JoinColumn(name="event", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event;
    
    /**
     * @ORM\Column(name="type", type="string", columnDefinition="ENUM('gold', 'silver', 'bronze')")
     */
    protected $type;

    /**
     * @ORM\Column(name="point", type="integer", length=11, nullable=false)
     */
    protected $point;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param mixed $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }

    /**
     * @return mixed
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
}
