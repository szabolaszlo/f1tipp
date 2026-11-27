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
        if ($facts->counts->isEmpty()) {
            return ['Nincs mit nullázni.'];
        }

        $reasons = [];

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

        if ($facts->resultsOnFutureEvents > 0) {
            $reasons[] = 'Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át.';
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
