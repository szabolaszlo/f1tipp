<?php

namespace App\Tests\Unit\Season;

use App\Season\SeasonCounts;
use App\Season\SeasonFacts;
use App\Season\SeasonResetGuard;
use PHPUnit\Framework\TestCase;

class SeasonResetGuardTest extends TestCase
{
    private const FUTURE_RESULT = 'Eredmény tartozik jövőbeli dátumú eseményhez. Az eseményeket a nullázás után írd át.';
    private const NOT_INNODB = 'Nem tranzakcióképes tábla (nem InnoDB): bet, trophy. A nullázás nem lenne visszavonható, ezért tiltva.';

    private SeasonResetGuard $guard;

    protected function setUp(): void
    {
        $this->guard = new SeasonResetGuard();
    }

    public function test_empty_season_only_reports_nothing_to_reset(): void
    {
        $facts = $this->facts(counts: new SeasonCounts(0, 0, 0, 0, 0, 0, 12), closingYear: 2025, archiveExists: false);

        $this->assertSame(['Nincs mit nullázni.'], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-02-01')));
    }

    public function test_missing_archive_blocks(): void
    {
        $facts = $this->facts(closingYear: 2025, archiveExists: false);

        $this->assertContains(
            'Hiányzik a „2025” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.',
            $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-02-01'))
        );
    }

    public function test_same_year_blocks_until_new_year(): void
    {
        $facts = $this->facts(closingYear: 2026);

        $this->assertSame(
            ['A 2026. évi szezon 2027. január 1-jétől nullázható.'],
            $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-15'))
        );
    }

    public function test_last_second_of_the_closing_year_still_blocks(): void
    {
        $facts = $this->facts(closingYear: 2026);

        $this->assertSame(
            ['A 2026. évi szezon 2027. január 1-jétől nullázható.'],
            $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-31 23:59:59'))
        );
    }

    public function test_new_year_allows(): void
    {
        $facts = $this->facts(closingYear: 2026);

        $this->assertSame([], $this->guard->blockReasons($facts, new \DateTimeImmutable('2027-01-01 00:00:00')));
    }

    public function test_result_on_future_event_blocks(): void
    {
        $facts = $this->facts(closingYear: 2026, resultsOnFutureEvents: 1);

        $this->assertSame([self::FUTURE_RESULT], $this->guard->blockReasons($facts, new \DateTimeImmutable('2027-03-01')));
    }

    // a jövőbe tett eredményes esemény miatt a closingYear megbízhatatlan: az archív- és évszám-ok félrevezető lenne
    public function test_result_on_future_event_hides_archive_and_year_reasons(): void
    {
        $facts = $this->facts(closingYear: 2026, archiveExists: false, resultsOnFutureEvents: 2);

        $this->assertSame([self::FUTURE_RESULT], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-15')));
    }

    public function test_archive_reason_comes_before_year_reason(): void
    {
        $facts = $this->facts(closingYear: 2026, archiveExists: false);

        $this->assertSame(
            [
                'Hiányzik a „2026” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.',
                'A 2026. évi szezon 2027. január 1-jétől nullázható.',
            ],
            $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-15'))
        );
    }

    public function test_non_innodb_table_blocks_an_otherwise_allowed_reset(): void
    {
        $facts = $this->facts(nonTransactionalTables: ['bet', 'trophy']);

        $this->assertSame([self::NOT_INNODB], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-02-01')));
    }

    public function test_non_innodb_reason_comes_first(): void
    {
        $facts = $this->facts(closingYear: 2026, archiveExists: false, nonTransactionalTables: ['bet', 'trophy']);

        $this->assertSame(
            [
                self::NOT_INNODB,
                'Hiányzik a „2026” archív Információ-bejegyzés. Amíg nincs meg, a nullázás tiltva.',
                'A 2026. évi szezon 2027. január 1-jétől nullázható.',
            ],
            $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-15'))
        );
    }

    public function test_non_innodb_reason_stays_next_to_future_result(): void
    {
        $facts = $this->facts(closingYear: 2026, archiveExists: false, resultsOnFutureEvents: 1, nonTransactionalTables: ['bet', 'trophy']);

        $this->assertSame([self::NOT_INNODB, self::FUTURE_RESULT], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-12-15')));
    }

    public function test_non_innodb_reason_stays_next_to_nothing_to_reset(): void
    {
        $facts = $this->facts(counts: new SeasonCounts(0, 0, 0, 0, 0, 0, 12), nonTransactionalTables: ['bet', 'trophy']);

        $this->assertSame([self::NOT_INNODB, 'Nincs mit nullázni.'], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-02-01')));
    }

    public function test_bets_only_needs_no_archive(): void
    {
        $facts = $this->facts(counts: new SeasonCounts(5, 10, 0, 0, 0, 0, 3), closingYear: null, archiveExists: false);

        $this->assertSame([], $this->guard->blockReasons($facts, new \DateTimeImmutable('2026-02-01')));
    }

    public function test_future_bets_warn(): void
    {
        $facts = $this->facts(betsOnFutureEvents: 3);

        $this->assertSame(['3 tipp jövőbeli eseményre szól, ezek is törlődnek.'], $this->guard->warnings($facts));
    }

    public function test_no_future_bets_no_warning(): void
    {
        $this->assertSame([], $this->guard->warnings($this->facts()));
    }

    private function facts(
        ?SeasonCounts $counts = null,
        ?int $closingYear = 2025,
        bool $archiveExists = true,
        int $resultsOnFutureEvents = 0,
        int $betsOnFutureEvents = 0,
        array $nonTransactionalTables = [],
    ): SeasonFacts {
        return new SeasonFacts(
            $counts ?? new SeasonCounts(10, 20, 5, 10, 1, 1, 3),
            $closingYear,
            $closingYear === null ? null : $closingYear + 1,
            $archiveExists,
            true,
            $resultsOnFutureEvents,
            $betsOnFutureEvents,
            $nonTransactionalTables,
        );
    }
}
