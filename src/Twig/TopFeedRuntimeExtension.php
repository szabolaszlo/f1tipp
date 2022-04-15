<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class TopFeedRuntimeExtension implements RuntimeExtensionInterface
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
    public function renderTopFeed(): string
    {
        $feeds = $this->entityManager
            ->getRepository('App:Feed')
            ->findBy(array(), array('id' => 'DESC'), 1);

        $feed = is_array($feeds) && isset($feeds[0]) ? $feeds[0] : array();

        return $this->twig->render('extension/top_feed.html.twig', [
            'id' => 'topFeed',
            'feed' => $feed
        ]);
    }

}