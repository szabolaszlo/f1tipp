<?php

namespace App\Twig;

use App\Entity\Event;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class CountDownRuntimeExtension implements RuntimeExtensionInterface
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
    public function renderCountDown(): string
    {
        $events = $this->entityManager->getRepository('App:Event')->getActualWeekendEvents();

        $data = [];

        $now = new DateTimeImmutable();

        /** @var Event $event */
        foreach ($events as $event) {
            $data[$event->getType()] = [
                'id' => $event->getType(),
                'name' => $event->getName(),
                'date' => $event->getDateTime()->format('M.d H:i'),
                'remain_time' => $now->diff($event->getDateTime())
            ];
        }

        return $this->twig->render("extension/count_down.html.twig",
            [
                'events' => $data,
                'details_link' => '/#/calendar',
                'id' => 'count_down',
            ]
        );
    }
}
