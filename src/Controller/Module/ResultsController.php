<?php

/**
 * Created by PhpStorm.
 * User: Carlos
 * Date: 2016. 12. 26.
 * Time: 20:41
 */

namespace App\Controller\Module;

use App\Cache\FileCache;
use App\LegacyService\ResultTable\ResultTable;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class Results
 * @package App\Controller\Page\Results
 */
class ResultsController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    /**
     * @param ResultTable $resultTable
     * @param FileCache $cache
     * @return Response
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Route(path: '/module/results', name: 'results', methods: ['GET'])]
    public function indexAction(ResultTable $resultTable, FileCache $cache): Response
    {
        // a kulcshoz elég a COUNT, a teljes listát csak cache-miss esetén töltjük be
        $resultRepository = $this->doctrine->getRepository(\App\Entity\Result::class);
        $cacheKey = 'results_v2_' . $resultRepository->getResultCount();

        $cacheData = $cache->get($cacheKey);

        $weekends = $cacheData[0] ?? [];

        $weekendNames = $cacheData[1] ?? [];

        if (empty($weekends)) {
            foreach ($resultRepository->findAll() as $result) {
                $weekends[$result->getEvent()->getWeekendOrder()][] = $resultTable
                    ->getTable($this->getUser(), $result->getEvent(), 'full')
                    ->renderTable($result->getEvent());

                $weekendNames[$result->getEvent()->getWeekendOrder()] = $result->getEvent()->getName();

                if ($result->getEvent()->getType() == 'race') {
                    $weekends[$result->getEvent()->getWeekendOrder()]['summary'] =
                        $resultTable->getTable($this->getUser(), $result->getEvent(), 'summary')->renderTable(
                            $result->getEvent(),
                        );
                }
            }

            $cache->save($cacheKey, [0 => $weekends, 1 => $weekendNames]);
        }

        return $this->render('controller/module/results.html.twig', [
            'id' => 'results',
            'weekends' => $weekends,
            'weekendNames' => $weekendNames
        ]);
    }
}
