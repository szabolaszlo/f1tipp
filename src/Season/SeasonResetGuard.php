<?php

namespace App\Season;

/**
 * Eldönti, hogy a szezon nullázható-e, és mire figyelmeztessen.
 */
final class SeasonResetGuard
{
    /**
     * @return list<string> a tiltások okai; üres, ha a nullázás engedélyezett
     */
    public function blockReasons(SeasonFacts $facts, \DateTimeImmutable $now): array
    {
        $reasons = [];

        if ($facts->nonTransactionalTables !== []) {
            $reasons[] = sprintf(
                'Nem tranzakcióképes tábla (nem InnoDB): %s. A nullázás nem lenne visszavonható, ezért tiltva.',
                implode(', ', $facts->nonTransactionalTables)
            );
        }

        if ($facts->counts->isEmpty()) {
            return [...$reasons, 'Nincs mit nullázni.'];
        }

        // a jövőbe tett eredményes esemény miatt a closingYear megbízhatatlan: az archív- és évszám-ok félrevezető lenne
        if ($facts->resultsOnFutureEvents > 0) {
            return [...$reasons, 'Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át.'];
        }

        if ($facts->closingYear !== null) {
            if (!$facts->archiveExists) {
                $reasons[] = sprintf(
                    'Hiányzik a „%d” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.',
                    $facts->closingYear
                );
            }

            if ((int) $now->format('Y') <= $facts->closingYear) {
                $reasons[] = sprintf(
                    'A %d. évi szezon %d. január 1-jétől nullázható.',
                    $facts->closingYear,
                    $facts->closingYear + 1
                );
            }
        }

        return $reasons;
    }

    /**
     * @return list<string>
     */
    public function warnings(SeasonFacts $facts): array
    {
        if ($facts->betsOnFutureEvents > 0) {
            return [sprintf('%d tipp jövőbeli eseményre szól, ezek is törlődnek.', $facts->betsOnFutureEvents)];
        }

        return [];
    }
}
