<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class User
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
     * @ORM\Column(name="password", type="string", length=60, nullable=false)
     */
    protected $password;

    /**
     * @ORM\OneToMany(targetEntity="UserAuthentication", mappedBy="user", cascade={"persist","remove"})
     */
    protected $auth;

    /**
     * @ORM\OneToMany(targetEntity="Trophy", mappedBy="user", cascade={"persist","remove"})
     */
    protected $trophies;

    /**
     * @ORM\OneToMany(targetEntity="UserSkippedNews", mappedBy="user", cascade={"persist","remove"})
     */
    protected $skippedNews;

    /**
     * @ORM\Column(name="timestamp", type="integer", length=11, nullable=false)
     */
    protected $timestamp;

    /**
     * @ORM\Column(name="admin", type="boolean", length=1, nullable=true)
     */
    protected $isAdmin;

    /**
     * @var int
     */
    protected $point = 0;

    /**
     * @var string
     */
    protected $pointDifference = '';

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->skippedNews = new ArrayCollection();
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
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
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
    public function getPointDifference()
    {
        return $this->pointDifference;
    }

    /**
     * @param string $pointDifference
     */
    public function setPointDifference($pointDifference)
    {
        $this->pointDifference = $pointDifference;
    }

    /**
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @return mixed
     */
    public function getTrophies()
    {
        return $this->trophies;
    }

    /**
     * @return array
     */
    public function getPodiumTrophies()
    {
        $trophies = array();

        /** @var Trophy $trophy */
        foreach ($this->trophies as $trophy) {
            $trophies[$trophy->getType()][] = $trophy;
        }

        return array_replace(array_flip(array('gold', 'silver', 'bronze')), $trophies);
    }

    /**
     * @return mixed
     */
    public function getSkippedNews()
    {
        $skippedInformation = new ArrayCollection();

        /** @var UserSkippedNews $skipped */
        foreach ($this->skippedNews as $skipped) {
            $skippedInformation->add($skipped->getInformation());
        }
        return $skippedInformation;
    }
}
