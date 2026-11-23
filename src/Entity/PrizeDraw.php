<?php

namespace App\Entity;

use App\Repository\PrizeDrawRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A Nyeremény modul beállítása: egyetlen sor (id = 1), az admin "Nyeremény" menüjében szerkeszthető.
 * Nyitott nevezésnél a modul csak a bajnokság tagjainak számát mutatja, lezártnál a helyezés => összeg sorokat
 * és a megjegyzést.
 */
#[ORM\Entity(repositoryClass: PrizeDrawRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table(name: 'prize_draw')]
class PrizeDraw
{
    /** az egyetlen sor id-ja (docs/sql/2026-11-23-prize-draw.sql) */
    public const ID = 1;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: 'entry_open', type: 'boolean', options: ['default' => 0])]
    private bool $entryOpen = false;

    #[ORM\Column(name: 'note', type: 'text', nullable: true)]
    private ?string $note = null;

    /** @var Collection<int, Prize> */
    #[ORM\OneToMany(targetEntity: Prize::class, mappedBy: 'prizeDraw', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['place' => 'ASC'])]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
    #[Assert\Valid]
    private Collection $prizes;

    public function __construct()
    {
        $this->prizes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEntryOpen(): bool
    {
        return $this->entryOpen;
    }

    public function setEntryOpen(bool $entryOpen): self
    {
        $this->entryOpen = $entryOpen;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /** @return Collection<int, Prize> */
    public function getPrizes(): Collection
    {
        return $this->prizes;
    }

    /**
     * A sorok helyezés szerint növekvő sorrendben (új, még nem mentett sorok esetén is).
     *
     * @return Prize[]
     */
    public function getPrizesByPlace(): array
    {
        $prizes = $this->prizes->toArray();
        usort($prizes, fn(Prize $a, Prize $b) => $a->getPlace() <=> $b->getPlace());

        return $prizes;
    }

    public function addPrize(Prize $prize): self
    {
        if (!$this->prizes->contains($prize)) {
            $this->prizes->add($prize);
            $prize->setPrizeDraw($this);
        }

        return $this;
    }

    public function removePrize(Prize $prize): self
    {
        $this->prizes->removeElement($prize);

        return $this;
    }

    /**
     * Egy űrlapon belül két sor ugyanazzal a helyezéssel: a DB egyedi indexe helyett validációs hiba legyen.
     */
    #[Assert\Callback]
    public function validateUniquePlaces(ExecutionContextInterface $context): void
    {
        $seen = [];
        foreach ($this->prizes as $index => $prize) {
            $place = $prize->getPlace();
            if ($place === null) {
                continue;
            }
            if (isset($seen[$place])) {
                $context->buildViolation('Ehhez a helyezéshez már van összeg megadva.')
                    ->atPath(sprintf('prizes[%s].place', $index))
                    ->addViolation();
            }
            $seen[$place] = true;
        }
    }
}
