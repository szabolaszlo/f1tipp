<?php

namespace App\Tests\Unit\Display;

use App\Display\CompetitionRanking;
use PHPUnit\Framework\TestCase;

class CompetitionRankingTest extends TestCase
{
    public function test_ranks_with_ties(): void
    {
        self::assertSame(
            [1, 2, 3, 3, 5, 6, 7, 7, 9, 10, 10],
            (new CompetitionRanking())->rank([31, 25, 24, 24, 21, 19, 18, 18, 10, 0, 0])
        );
    }

    public function test_empty_input_returns_empty(): void
    {
        self::assertSame([], (new CompetitionRanking())->rank([]));
    }
}
