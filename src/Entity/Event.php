<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event")
 * @ORM\Table(name="`event`")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="event_type", type="string"))
 * @ORM\DiscriminatorMap({"event" = "Event", "race" = "Race", "qualify" = "Qualify"})
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Event
{
    /**
     * @ORM\Column(name="id", type="integer", length=11, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="weekend_order", type="integer", length=2, nullable=false)
     */
    protected $weekendOrder;

    /**
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(name="date_time", type="datetime", nullable=false)
     */
    protected $date_time;

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
    public function getWeekendOrder()
    {
        return $this->weekendOrder;
    }

    /**
     * @param mixed $weekendOrder
     */
    public function setWeekendOrder($weekendOrder)
    {
        $this->weekendOrder = $weekendOrder;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->date_time;
    }

    /**
     * @param mixed $date_time
     */
    public function setDateTime($date_time)
    {
        $this->date_time = $date_time;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'event';
    }
}
