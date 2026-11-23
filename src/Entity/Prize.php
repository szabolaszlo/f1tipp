<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Nyeremény modul egy sora: a helyezésért járó összeg (forint). A PrizeDraw-val együtt szerkeszthető.
 */
#[ORM\Entity]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table(name: 'prize')]
#[ORM\UniqueConstraint(name: 'UNIQ_PRIZE_DRAW_PLACE', columns: ['prize_draw_id', 'place'])]
class Prize
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PrizeDraw::class, inversedBy: 'prizes')]
    #[ORM\JoinColumn(name: 'prize_draw_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
    private ?PrizeDraw $prizeDraw = null;

    #[ORM\Column(name: 'place', type: 'integer')]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'A helyezés legalább 1.')]
    private ?int $place = null;

    #[ORM\Column(name: 'amount', type: 'integer')]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero(message: 'Az összeg nem lehet negatív.')]
    private ?int $amount = null;

    /** az admin kollekció elemeinek fejléce és a részletek oldal, pl. "1. helyezés: 24,000 Ft" (új, üres sornál üres) */
    public function __toString(): string
    {
        if ($this->place === null) {
            return '';
        }

        return sprintf('%d. helyezés: %s Ft', $this->place, number_format((int) $this->amount, 0, '.', ','));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrizeDraw(): ?PrizeDraw
    {
        return $this->prizeDraw;
    }

    public function setPrizeDraw(?PrizeDraw $prizeDraw): self
    {
        $this->prizeDraw = $prizeDraw;

        return $this;
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
