<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AppExtension
 * @package App\Twig
 */
class AppExtension extends AbstractExtension
{
    /**
     * @return array|TwigFunction|TwigFunction[]
     * Lazy extension: https://symfony.com/doc/4.4/templating/twig_extension.html#creating-lazy-loaded-twig-extensions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'result_table',
                [ResultTableRuntimeExtension::class, 'renderResultTable'],
                ['is_safe' => ['html']]),
            new TwigFunction(
                'topFeedExtension',
                [TopFeedRuntimeExtension::class, 'renderTopFeed'],
                ['is_safe' => ['html']]),
            new TwigFunction(
                'pointSummaryChartExtension',
                [PointSummaryChartRuntimeExtension::class, 'renderPointSummaryChart'],
                ['is_safe' => ['html']]),
            new TwigFunction(
                'countDownExtension',
                [CountDownRuntimeExtension::class, 'renderCountDown'],
                ['is_safe' => ['html']])
        ];
    }
}
