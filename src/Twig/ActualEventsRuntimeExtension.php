<?php

namespace App\Twig;

use App\Entity\Event;
use App\Entity\Race;
use App\LegacyService\ResultTable\ResultTable;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class ActualEventsRuntimeExtension implements RuntimeExtensionInterface
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
     * @var ResultTable
     */
    protected ResultTable $resultTable;

    /**
     * @param EntityManagerInterface $entityManager
     * @param Environment $twig
     * @param ResultTable $resultTable
     */
    public function __construct(EntityManagerInterface $entityManager, Environment $twig, ResultTable $resultTable)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->resultTable = $resultTable;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
    public function renderActualEvents($user): string
    {
        $events = $this->entityManager->getRepository('App:Event')->getActualWeekendEvents();

        $tables = [];

        $now = new DateTimeImmutable();

        /** @var Event $event */
        foreach ($events as $event) {
            $id = abs($now->getTimestamp() - $event->getDateTime()->getTimeStamp());
            $tables[$id] = $this->resultTable->getTable($user, $event)->renderTable($event);

            if ($event instanceof Race) {
                $race = $event;
            }
        }

        if (isset($race)) {
            $summaryTable = $this->resultTable
                ->getTable($user, $race, 'summary')
                ->renderTable($race);

            if ($summaryTable) {
                $tables[0] = $summaryTable;
            }
        }

        ksort($tables);

        return $this->twig->render('extension/actual_events_results.html.twig', [
            'tables' => $tables
        ]);
    }
}
