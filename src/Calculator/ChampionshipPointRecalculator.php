<?php

namespace App\Calculator;

use App\Calculator\Type\AlternativeCalculator;
use App\Calculator\Type\AlternativePointSummaryCalculator;
use App\Entity\AlternativeChampionship;
use Doctrine\ORM\EntityManagerInterface;

/**
 * A pontozási tábla változása után a már kiszámolt hétvégék újrapontozása: a tárolt hétvégi összpontokból
 * (collected_points) újra kiosztja a bajnoki pontokat, majd újraösszegzi a bajnokságot.
 * A tippek és a kupák nem változnak, ezért nem kell a teljes újraszámolás.
 */
class ChampionshipPointRecalculator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AlternativeCalculator $alternativeCalculator,
        private readonly AlternativePointSummaryCalculator $summaryCalculator
    ) {
    }

    /**
     * @return int az újrapontozott hétvégék száma
     */
    public function recalculate(): int
    {
        $races = $this->em->getRepository(AlternativeChampionship::class)->getCalculatedRaces();

        foreach ($races as $race) {
            $this->alternativeCalculator->calculateUsersAlternativePoints($race);
        }
        $this->em->flush();

        $this->summaryCalculator->calculate();

        return count($races);
    }
}
