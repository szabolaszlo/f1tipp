<?php

namespace App\Tests\Unit\Calculator;

use App\Calculator\ChampionshipPointConverter;
use App\Display\CompetitionRanking;
use PHPUnit\Framework\TestCase;

class ChampionshipPointConverterTest extends TestCase
{
    private const CURRENT_TABLE = [1 => 15, 2 => 11, 3 => 9, 4 => 7, 5 => 6, 6 => 5, 7 => 4, 8 => 2, 9 => 1, 10 => 0];

    private function convert(array $collectedPoints, array $placePoints): array
    {
        return (new ChampionshipPointConverter(new CompetitionRanking()))->convert($collectedPoints, $placePoints);
    }

    public function test_current_table(): void
    {
        self::assertSame([15, 11, 9], $this->convert([70, 67, 15], self::CURRENT_TABLE));
    }

    public function test_custom_table(): void
    {
        self::assertSame([25, 18, 15], $this->convert([70, 67, 15], [1 => 25, 2 => 18, 3 => 15]));
    }

    public function test_tie_shares_points_and_next_place_is_skipped(): void
    {
        self::assertSame([15, 15, 9], $this->convert([70, 70, 15], self::CURRENT_TABLE));
    }

    public function test_zero_collected_points_give_zero(): void
    {
        self::assertSame([15, 0, 0], $this->convert([70, 0, 0], self::CURRENT_TABLE));
    }

    public function test_places_outside_the_table_give_zero(): void
    {
        self::assertSame([3, 1, 0], $this->convert([9, 8, 7], [1 => 3, 2 => 1]));
    }

    public function test_empty_table_gives_zero_for_everyone(): void
    {
        self::assertSame([0, 0], $this->convert([70, 67], []));
    }

    public function test_empty_input_returns_empty(): void
    {
        self::assertSame([], $this->convert([], self::CURRENT_TABLE));
    }
}
