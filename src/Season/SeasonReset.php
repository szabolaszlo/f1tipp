<?php

namespace App\Season;

use App\Cache\FileCache;
use App\Cache\ResultCache;
use App\Controller\Module\ResultsOfChampionshipController;
use Doctrine\ORM\EntityManagerInterface;

/**
 * A szezon nullázása: tippek, eredmények, kupák és alternatív pontok törlése, a userek pontjainak
 * nullázása egy tranzakcióban, utána minden cache ürítése. Az események és a pilóták érintetlenek.
 */
class SeasonReset
{
    /** a törlés sorrendje: előbb a gyerektáblák; DELETE, nem TRUNCATE (az tranzakción kívül, implicit commit-tal futna) */
    private const STATEMENTS = [
        'DELETE FROM bet_attribute',
        'DELETE FROM bet',
        'DELETE FROM result_attribute',
        'DELETE FROM result',
        'DELETE FROM alternative_championship',
        'DELETE FROM trophy',
        'UPDATE `user` SET point_summary = 0, point_difference = NULL, alternative_point_summary = NULL, alternative_point_difference = NULL',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FileCache $fileCache,
        private readonly SeasonFactsProvider $factsProvider,
    ) {
    }

    /**
     * @return SeasonCounts a nullázás előtti darabszámok (a tranzakción belül számolva)
     * @throws \Throwable bármely statement hibájakor, a rollback után; ilyenkor a cache-ek érintetlenek
     */
    public function reset(): SeasonCounts
    {
        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        try {
            $counts = $this->factsProvider->counts();

            foreach (self::STATEMENTS as $statement) {
                $connection->executeStatement($statement);
            }

            $connection->commit();
        } catch (\Throwable $e) {
            // a rollback hibája (pl. megszakadt kapcsolat) nem takarhatja el az eredeti kivételt
            if ($connection->isTransactionActive()) {
                try {
                    $connection->rollBack();
                } catch (\Throwable) {
                }
            }

            throw $e;
        }

        $this->clearCaches();

        return $counts;
    }

    private function clearCaches(): void
    {
        $this->em->clear();

        $cache = $this->em->getCache();
        if ($cache) {
            $cache->evictEntityRegions();
            $cache->evictCollectionRegions();
            $cache->evictQueryRegions();
        }

        ResultCache::of($this->em)->deleteAll();

        // a pilóta-VB állása nem a mi adatunk: ürítés nélkül a cronig élesen a RapidAPI-t hívná az oldalsáv
        $championship = $this->fileCache->get(ResultsOfChampionshipController::RESULTS_CACHE_KEY);
        $this->fileCache->clearAll();
        if (is_array($championship) && $championship !== []) {
            $this->fileCache->save(ResultsOfChampionshipController::RESULTS_CACHE_KEY, $championship);
        }
    }
}
