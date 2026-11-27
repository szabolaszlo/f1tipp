<?php

namespace App\Season;

/**
 * A szezonváltáskor törlődő (illetve nullázódó) sorok száma.
 */
final class SeasonCounts
{
    public function __construct(
        public readonly int $bets,
        public readonly int $betAttributes,
        public readonly int $results,
        public readonly int $resultAttributes,
        public readonly int $trophies,
        public readonly int $alternatives,
        public readonly int $users,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->bets + $this->results + $this->trophies + $this->alternatives === 0;
    }
}
