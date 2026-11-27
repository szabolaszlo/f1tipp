<?php

namespace App\Season;

use Doctrine\ORM\EntityManagerInterface;

/**
 * A szezonváltás tényeit tölti be az adatbázisból (csak olvas).
 */
class SeasonFactsProvider
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function load(\DateTimeImmutable $now): SeasonFacts
    {
        $connection = $this->em->getConnection();
        $nowParam = $now->format('Y-m-d H:i:s');

        $closingYear = $this->nullableInt($connection->fetchOne(
            'SELECT YEAR(MAX(e.date_time)) FROM `event` e INNER JOIN result r ON r.event = e.id'
        ));
        $currentYear = $this->nullableInt($connection->fetchOne('SELECT YEAR(MAX(date_time)) FROM `event`'));

        return new SeasonFacts(
            $this->counts(),
            $closingYear,
            $currentYear,
            $closingYear !== null && $this->informationExists((string) $closingYear),
            $closingYear !== null && $this->informationExists('alter-' . $closingYear),
            (int) $connection->fetchOne(
                'SELECT COUNT(*) FROM result r INNER JOIN `event` e ON e.id = r.event WHERE e.date_time > ?',
                [$nowParam]
            ),
            (int) $connection->fetchOne(
                'SELECT COUNT(*) FROM bet b INNER JOIN `event` e ON e.id = b.event_id WHERE e.date_time > ?',
                [$nowParam]
            ),
        );
    }

    /**
     * A szezonváltáskor törlődő, illetve nullázódó sorok száma.
     */
    public function counts(): SeasonCounts
    {
        $row = $this->em->getConnection()->fetchAssociative(
            'SELECT
                (SELECT COUNT(*) FROM bet) AS bets,
                (SELECT COUNT(*) FROM bet_attribute) AS bet_attributes,
                (SELECT COUNT(*) FROM result) AS results,
                (SELECT COUNT(*) FROM result_attribute) AS result_attributes,
                (SELECT COUNT(*) FROM trophy) AS trophies,
                (SELECT COUNT(*) FROM alternative_championship) AS alternatives,
                (SELECT COUNT(*) FROM `user`) AS users'
        );

        return new SeasonCounts(
            (int) $row['bets'],
            (int) $row['bet_attributes'],
            (int) $row['results'],
            (int) $row['result_attributes'],
            (int) $row['trophies'],
            (int) $row['alternatives'],
            (int) $row['users'],
        );
    }

    private function informationExists(string $slug): bool
    {
        return $this->em->getConnection()->fetchOne('SELECT 1 FROM information WHERE slug = ?', [$slug]) !== false;
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === false ? null : (int) $value;
    }
}
