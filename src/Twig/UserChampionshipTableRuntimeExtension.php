<?php

namespace App\Twig;

use App\Calculator\Provider\PointProvider;
use App\Calculator\UserChampionshipOrderResolver\UserChampionshipOrderResolver;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class UserChampionshipTableRuntimeExtension implements RuntimeExtensionInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var Environment
     */
    protected Environment $twig;

    protected UserChampionshipOrderResolver $userChampionshipOrderResolver;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Environment $twig
     * @param UserChampionshipOrderResolver $userChampionshipOrderResolver
     */
    public function __construct(EntityManagerInterface $entityManager, Environment $twig, UserChampionshipOrderResolver $userChampionshipOrderResolver)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->userChampionshipOrderResolver = $userChampionshipOrderResolver;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderUserChampionshipTable(): string
    {
        return $this->twig->render(
            'extension/user_championship.html.twig',
            [
                'users' => $this
                    ->userChampionshipOrderResolver
                    ->getUserChampionShipOrder(),
                'records' => [
                    'qualify_bets' => $this->entityManager->getRepository(\App\Entity\Bet::class)->getTopQualifyBets(),
                    'race_bets' => $this->entityManager->getRepository(\App\Entity\Bet::class)->getTopRaceBets(),
                    'weekend_bets' => $this->entityManager->getRepository(\App\Entity\Trophy::class)->getTopWeekendUsers()
                ],
                'maths' => [
                    'remaining_weekends' => $this->entityManager->getRepository(\App\Entity\Race::class)->getRemainEvents()
                ],
                'details_link' => '/#/results',
                'pointProvider' => new PointProvider(),
                'id' => 'userChampionship'
            ]
        );
    }
}
