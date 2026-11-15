<?php

namespace App\Twig;

use App\Display\BetLegendProvider;
use App\Display\CompetitionRanking;
use App\Display\TeamColorProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Megjelenítési helperek a „TV-grafika” táblákhoz.
 */
class DisplayExtension extends AbstractExtension
{
    private TeamColorProvider $teamColors;
    private CompetitionRanking $ranking;
    private BetLegendProvider $legend;

    public function __construct(TeamColorProvider $teamColors, CompetitionRanking $ranking, BetLegendProvider $legend)
    {
        $this->teamColors = $teamColors;
        $this->ranking = $ranking;
        $this->legend = $legend;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('team_color', [$this->teamColors, 'colorForDriver']),
            new TwigFunction('team_color_by_name', [$this->teamColors, 'colorForTeam']),
            new TwigFunction('competition_ranks', [$this->ranking, 'rank']),
            new TwigFunction('bet_legend', [$this->legend, 'forEventType']),
        ];
    }
}
