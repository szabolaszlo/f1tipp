<?php

namespace App\Tests\Integration\Display;

use App\Display\BetLegendProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BetLegendProviderTest extends KernelTestCase
{
    private BetLegendProvider $provider;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->provider = static::getContainer()->get(BetLegendProvider::class);
    }

    public function test_race_legend(): void
    {
        self::assertSame(
            ['exact' => 3, 'near' => 1, 'question' => 3, 'positions' => 10],
            $this->provider->forEventType('race')
        );
    }

    public function test_qualify_legend(): void
    {
        self::assertSame(
            ['exact' => 4, 'near' => 1, 'question' => null, 'positions' => 10],
            $this->provider->forEventType('qualify')
        );
    }

    public function test_sprint_legend(): void
    {
        self::assertSame(
            ['exact' => 1, 'near' => 0, 'question' => null, 'positions' => 8],
            $this->provider->forEventType('sprint_qualify')
        );
    }
}
