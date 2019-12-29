<?php
/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 09.
 * Time: 19:55
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\OneToMany(targetEntity="App\Entity\AlternativeChampionship", mappedBy="race", orphanRemoval=true)
     * @ORM\OrderBy({"points" = "DESC"})
     */
    private $alternativeChampionships;

    /**
     * Race constructor.
     */
    public function __construct()
    {
        $this->trophies = new ArrayCollection();
        $this->alternativeChampionships = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'race';
    }

    /**
     * @return ArrayCollection
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

    /**
     * @return Collection|AlternativeChampionship[]
     */
    public function getAlternativeChampionships(): Collection
    {
        return $this->alternativeChampionships;
    }

    public function addAlternativeChampionship(AlternativeChampionship $alternativeChampionship): self
    {
        if (!$this->alternativeChampionships->contains($alternativeChampionship)) {
            $this->alternativeChampionships[] = $alternativeChampionship;
            $alternativeChampionship->setRace($this);
        }

        return $this;
    }

    public function removeAlternativeChampionship(AlternativeChampionship $alternativeChampionship): self
    {
        if ($this->alternativeChampionships->contains($alternativeChampionship)) {
            $this->alternativeChampionships->removeElement($alternativeChampionship);
            // set the owning side to null (unless already changed)
            if ($alternativeChampionship->getRace() === $this) {
                $alternativeChampionship->setRace(null);
            }
        }

        return $this;
    }
}
