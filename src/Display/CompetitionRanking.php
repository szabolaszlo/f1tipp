<?php

namespace App\Display;

/**
 * "Standard competition" helyezés: azonos pontszám azonos helyezés, utána ugrik (1, 2, 3, 3, 5).
 */
class CompetitionRanking
{
    /**
     * @param array<int, int|float> $scores csökkenő sorrendű pontszámok
     * @return int[] azonos indexű helyezések
     */
    public function rank(array $scores): array
    {
        $ranks = [];
        $previous = null;
        $place = 0;

        foreach (array_values($scores) as $index => $score) {
            if ($previous === null || $score != $previous) {
                $place = $index + 1;
                $previous = $score;
            }
            $ranks[] = $place;
        }

        return $ranks;
    }
}
