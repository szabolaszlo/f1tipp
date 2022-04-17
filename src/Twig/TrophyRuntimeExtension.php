<?php

namespace App\Twig;

use App\Entity\Trophy;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class TrophyRuntimeExtension implements RuntimeExtensionInterface
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
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderTrophyModule(): string
    {
        $lastTrophies = $this
                ->entityManager
                ->getRepository('App:Trophy')
                ->getLastEventPodiumTrophies() ?? [];

        $podiumTrophies = [];

        $event = null;

        /** @var Trophy $trophy */
        foreach ($lastTrophies as $trophy) {
            $podiumTrophies[$trophy->getType()][] = $trophy;
            $event = $trophy->getEvent();
        }

        return $this->twig->render(
            'controller/module/trophies.html.twig',
            [
                'podium_trophies' => $podiumTrophies,
                'event' => $event,
                'details_link' => '/trophies',
                'id' => 'trophies_module'
            ]
        );
    }
}