<?php

namespace App\Display;

/**
 * Pilótakód / API csapatnév -> csapatszín a megjelenítéshez.
 * Forrás: config/packages/f1tipp_teams.yaml (f1tipp.teams paraméter).
 */
class TeamColorProvider
{
    public const NEUTRAL = '#6b6b78';

    /** @var array<int, array{name: string, color: string, match: string[], drivers: string[]}> */
    private array $teams;

    /** @var array<string, string> */
    private array $driverColors = [];

    public function __construct(array $teams)
    {
        $this->teams = $teams;

        foreach ($teams as $team) {
            foreach ($team['drivers'] as $code) {
                $this->driverColors[strtoupper($code)] = $team['color'];
            }
        }
    }

    public function colorForDriver(string $code): string
    {
        return $this->driverColors[strtoupper(trim($code))] ?? self::NEUTRAL;
    }

    public function colorForTeam(string $apiTeamName): string
    {
        $name = mb_strtolower($apiTeamName);

        foreach ($this->teams as $team) {
            foreach ($team['match'] as $needle) {
                if ($needle !== '' && strpos($name, $needle) !== false) {
                    return $team['color'];
                }
            }
        }

        return self::NEUTRAL;
    }
}
