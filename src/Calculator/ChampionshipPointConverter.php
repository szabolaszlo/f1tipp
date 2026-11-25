<?php

namespace App\Calculator;

use App\Display\CompetitionRanking;

/**
 * Hétvégi összpontok → bajnoki pontok a pontozási tábla (helyezés => pont) szerint.
 * Holtversenyben azonos pont jár, a következő helyezés ugrik (1, 1, 3); a táblában nem szereplő
 * helyezés és a 0 gyűjtött pont 0 bajnoki pontot ér.
 */
class ChampionshipPointConverter
{
    public function __construct(private readonly CompetitionRanking $ranking)
    {
    }

    /**
     * @param int[] $collectedPoints csökkenő sorrendű hétvégi összpontok
     * @param array<int, int> $placePoints helyezés => bajnoki pont
     * @return int[] azonos indexű bajnoki pontok
     */
    public function convert(array $collectedPoints, array $placePoints): array
    {
        $collectedPoints = array_values($collectedPoints);
        $points = [];

        foreach ($this->ranking->rank($collectedPoints) as $index => $place) {
            $points[] = $collectedPoints[$index] > 0 ? (int) ($placePoints[$place] ?? 0) : 0;
        }

        return $points;
    }
}
