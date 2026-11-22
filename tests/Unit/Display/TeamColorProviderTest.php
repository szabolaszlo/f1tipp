<?php

namespace App\Tests\Unit\Display;

use App\Display\TeamColorProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class TeamColorProviderTest extends TestCase
{
    private TeamColorProvider $provider;

    protected function setUp(): void
    {
        $config = Yaml::parseFile(__DIR__ . '/../../../config/packages/f1tipp_teams.yaml');
        $this->provider = new TeamColorProvider($config['parameters']['f1tipp.teams']);
    }

    public function test_known_driver_returns_team_color(): void
    {
        self::assertSame('#3671C6', $this->provider->colorForDriver('VER'));
        self::assertSame('#3671C6', $this->provider->colorForDriver('ver'));
        self::assertSame('#FF8000', $this->provider->colorForDriver('NOR'));
    }

    public function test_unknown_driver_gets_neutral_color(): void
    {
        self::assertSame('#6b6b78', $this->provider->colorForDriver('DOO'));
        self::assertSame(TeamColorProvider::NEUTRAL, $this->provider->colorForDriver(''));
    }

    public function test_team_name_matches_substring(): void
    {
        self::assertSame('#FF8000', $this->provider->colorForTeam('McLaren Racing'));
        self::assertSame('#6692FF', $this->provider->colorForTeam('Visa Cash App RB F1 Team'));
        self::assertSame('#6692FF', $this->provider->colorForTeam('Racing Bulls'));
        self::assertSame('#3671C6', $this->provider->colorForTeam('Red Bull Racing'));
    }

    public function test_unknown_team_gets_neutral_color(): void
    {
        self::assertSame('#6b6b78', $this->provider->colorForTeam('Minardi'));
    }
}
