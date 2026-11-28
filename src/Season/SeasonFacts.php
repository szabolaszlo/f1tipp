<?php

namespace App\Season;

/**
 * A szezonváltás engedélyezéséhez szükséges tények (az adatbázisból töltve).
 */
final class SeasonFacts
{
    public function __construct(
        public readonly SeasonCounts $counts,
        public readonly ?int $closingYear,
        public readonly ?int $currentYear,
        public readonly bool $archiveExists,
        public readonly bool $alterArchiveExists,
        public readonly int $resultsOnFutureEvents,
        public readonly int $betsOnFutureEvents,
        /** @var list<string> a nullázott táblák közül a nem InnoDB-k (azokon nincs tranzakció, a rollback nem állítaná vissza) */
        public readonly array $nonTransactionalTables,
    ) {
    }
}
