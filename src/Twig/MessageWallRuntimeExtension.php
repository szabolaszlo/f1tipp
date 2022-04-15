<?php

namespace App\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class MessageWallRuntimeExtension implements RuntimeExtensionInterface
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
     * @param $isUserLogged
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderMessageWall($isUserLogged): string
    {
        if ($isUserLogged) {
            return $this->twig->render(
                'controller/module/message_wall/message_wall.html.twig',
                [
                    'id' => 'messageWall',
                    'messages' => $this->entityManager->getRepository('App:Message')->getMessages()
                ]
            );
        } else {
            return '';
        }
    }
}
