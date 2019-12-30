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
                'alternative_championship_module',
                [AlternativeChampionshipRuntimeExtension::class, 'renderAlternativeChampionship'],
                ['is_safe' => ['html']])
        ];
    }
}
