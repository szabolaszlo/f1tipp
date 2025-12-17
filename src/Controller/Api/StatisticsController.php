<?php

namespace App\Controller\Api;

use App\Cache\FileCache;
use App\LegacyService\Statistics\ObjectSorter;
use App\LegacyService\Statistics\StatisticsCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class StatisticsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ObjectSorter $sorter;
    private StatisticsCalculator $calculator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ObjectSorter $sorter,
        StatisticsCalculator $calculator
    ) {
        $this->entityManager = $entityManager;
        $this->sorter = $sorter;
        $this->calculator = $calculator;
    }

    /**
     * Get betting and result statistics
     * 
     * @Route("/statistics", name="statistics", methods={"GET"})
     * @throws InvalidArgumentException
     */
    public function index(FileCache $cache): JsonResponse
    {
        $bets = $this->entityManager->getRepository('App:Bet')->findAll();
        $results = $this->entityManager->getRepository('App:Result')->findAll();

        $cacheKey = 'statistics_api_' . count($results);
        $data = $cache->get($cacheKey);

        if (empty($data)) {
            $data = [
                'statistics' => [
                    'bets' => $this->getStatistics($bets),
                    'results' => $this->getStatistics($results),
                ],
            ];
            $cache->save($cacheKey, $data);
        }

        return $this->json($data);
    }

    /**
     * @param array $objects
     * @return array
     * @throws \Exception
     */
    private function getStatistics(array $objects): array
    {
        $statistics = [];

        $this->sorter->addObjects($objects);

        $qualify = $this->sorter->getObjectsByType('qualify');
        $race = $this->sorter->getObjectsByType('race');

        $statistics['qualify'] = $this->calculator->getStatistics($qualify);
        $statistics['race'] = $this->calculator->getStatistics($race);

        return $statistics;
    }
}
