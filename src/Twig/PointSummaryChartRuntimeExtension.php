<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class PointSummaryChartRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * @param EntityManagerInterface $entitryManager
     * @param Environment $twig
     */
    public function __construct(EntityManagerInterface $entitryManager, Environment $twig)
    {
        $this->entityManager = $entitryManager;
        $this->twig = $twig;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function renderPointSummaryChart(): string
    {
        $races = $this->entityManager->getRepository('App:Race')->findAll();

        $pointHistory = [];
        $eventHistory = ['Start'];

        foreach ($races as $race) {
            $alter = $this
                ->entityManager
                ->getRepository('App:AlternativeChampionship')
                ->getAlternativeChampionshipResultByRace($race);
            if (!empty($alter)) {
                $eventHistory[] = $race->getWeekendOrder() . ' - ' . $race->getName();
            }
            foreach ($alter as $a) {
                $lastPoint = isset($pointHistory[$a->getUser()->getName()]) ? end($pointHistory[$a->getUser()->getName()]) : 0;
                $pointHistory[$a->getUser()->getName()][] = $lastPoint + $a->getPoints();
            }
        }

        foreach ($pointHistory as $key => $value) {
            array_unshift($pointHistory[$key], 0);
        }

        $users = $this->entityManager->getRepository('App:User')->getAlternativeChampionshipUsers();

        $sortMap = [];
        foreach ($users as $user) {
            $sortMap[$user->getName()] = $user->getAlternativePointSummary();
        }

        $pointHistorySorted = [];
        foreach ($sortMap as $name => $item) {
            if (isset($pointHistory[$name])) {
                $pointHistorySorted[$name] = $pointHistory[$name];
            }
        }

        if (empty($pointHistory)) {
            return '';
        }

        return $this->twig->render("extension/point_summary_chart_js.html.twig", [
            'id' => 'point_summary_chart',
            'eventHistory' => $eventHistory,
            'pointHistory' => $pointHistorySorted
        ]);
    }
}
