<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 19:55
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Race extends Event
{
    /**
     * @ORM\OneToMany(targetEntity="Trophy", mappedBy="event", cascade={"persist","remove"})
     */
    protected $trophies;

    /**
     * Race constructor.
     */
    public function __construct()
    {
        $this->trophies = new ArrayCollection();
    }
    /**
     * @return mixed
     */
    public function getTrophies()
    {
        return $this->trophies;
    }

    /**
     * @param mixed $trophies
     */
    public function setTrophies($trophies)
    {
        $this->trophies = $trophies;
    }
}
