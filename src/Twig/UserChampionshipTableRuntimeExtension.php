<?php

namespace App\Twig;

use App\Calculator\Provider\PointProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
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

    /**
     * @param EntityManagerInterface $entityManager
     * @param Environment $twig
     */
    public function __construct(EntityManagerInterface $entityManager, Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
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
                    ->entityManager
                    ->getRepository('App\Entity\User')
                    ->getAlternativeChampionshipUsers(),
                'records' => [
                    'qualify_bets' => $this->entityManager->getRepository('App:Bet')->getTopQualifyBets(),
                    'race_bets' => $this->entityManager->getRepository('App:Bet')->getTopRaceBets()
                ],
                'maths' => [
                    'remaining_weekends' => $this->entityManager->getRepository('App:Race')->getRemainEvents()
                ],
                'details_link' => '/#/results',
                'pointProvider' => new PointProvider(),
                'id' => 'userChampionship'
            ]
        );
    }
}
