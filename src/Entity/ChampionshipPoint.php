<?php

namespace App\Entity;

use App\Repository\ChampionshipPointRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A bajnoki pontozási tábla egy sora: a hétvégi helyezésért járó bajnoki pont.
 * Adminban szerkeszthető; mentéskor a már kiszámolt hétvégék újrapontozódnak (ChampionshipPointSubscriber).
 */
#[ORM\Entity(repositoryClass: ChampionshipPointRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table(name: 'championship_point')]
#[ORM\UniqueConstraint(name: 'UNIQ_CHAMPIONSHIP_POINT_PLACE', columns: ['place'])]
#[UniqueEntity(fields: ['place'], message: 'Ehhez a helyezéshez már van pont megadva.')]
class ChampionshipPoint
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: 'place', type: 'integer')]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'A helyezés legalább 1.')]
    private ?int $place = null;

    #[ORM\Column(name: 'points', type: 'integer')]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero(message: 'A pont nem lehet negatív.')]
    private ?int $points = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(?int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }
}
