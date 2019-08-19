<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 20:44
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`driver`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Driver
{
    /**
     * @ORM\Column(name="id", type="integer", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(name="short", type="string", length=3, nullable=false)
     */
    protected $short;

    /**
     * @ORM\Column(name="point", type="integer", length=3, nullable=false)
     */
    protected $point;

    /**
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    protected $status;

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
     * @return mixed
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @param mixed $short
     */
    public function setShort($short)
    {
        $this->short = $short;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
