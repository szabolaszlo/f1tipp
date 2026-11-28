<?php

namespace App\Tests\Unit\Season;

use App\Cache\FileCache;
use App\Controller\Module\ResultsOfChampionshipController;
use App\Season\SeasonFactsProvider;
use App\Season\SeasonReset;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Cache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class SeasonResetTest extends TestCase
{
    private const EXPECTED_STATEMENTS = [
        'DELETE FROM bet_attribute',
        'DELETE FROM bet',
        'DELETE FROM result_attribute',
        'DELETE FROM result',
        'DELETE FROM alternative_championship',
        'DELETE FROM trophy',
        'UPDATE `user` SET point_summary = 0, point_difference = NULL, alternative_point_summary = NULL, alternative_point_difference = NULL',
    ];

    private Connection&MockObject $connection;
    private EntityManagerInterface&MockObject $em;
    private FileCache&MockObject $fileCache;
    private CacheItemPoolInterface&MockObject $resultCachePool;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('fetchAssociative')->willReturn([
            'bets' => '12', 'bet_attributes' => '40', 'results' => '4', 'result_attributes' => '30',
            'trophies' => '9', 'alternatives' => '6', 'users' => '3',
        ]);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getConnection')->willReturn($this->connection);

        $this->resultCachePool = $this->createMock(CacheItemPoolInterface::class);
        $configuration = $this->createMock(Configuration::class);
        $configuration->method('getResultCache')->willReturn($this->resultCachePool);
        $this->em->method('getConfiguration')->willReturn($configuration);

        $this->fileCache = $this->createMock(FileCache::class);
    }

    public function test_failure_rolls_back_and_skips_cache_clear(): void
    {
        $calls = 0;
        $this->connection->method('executeStatement')->willReturnCallback(function () use (&$calls) {
            if (++$calls === 4) {
                throw new \RuntimeException('DB hiba');
            }

            return 1;
        });

        $this->connection->method('isTransactionActive')->willReturn(true);
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('rollBack');
        $this->connection->expects($this->never())->method('commit');
        $this->em->expects($this->never())->method('clear');
        $this->em->expects($this->never())->method('getCache');
        $this->resultCachePool->expects($this->never())->method('clear');
        $this->fileCache->expects($this->never())->method('clearAll');
        $this->fileCache->expects($this->never())->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB hiba');

        $this->reset()->reset();
    }

    // a rollback hibája (pl. megszakadt kapcsolat) nem takarhatja el az eredeti kivételt
    public function test_failing_rollback_does_not_hide_the_original_exception(): void
    {
        $this->connection->method('executeStatement')->willThrowException(new \RuntimeException('DB hiba'));
        $this->connection->method('isTransactionActive')->willReturn(true);
        $this->connection->expects($this->once())->method('rollBack')->willThrowException(new \LogicException('rollback hiba'));
        $this->fileCache->expects($this->never())->method('clearAll');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB hiba');

        $this->reset()->reset();
    }

    // ha a tranzakció már nem él (a szerver visszagörgette), nincs mit visszagörgetni: az eredeti kivétel megy tovább
    public function test_inactive_transaction_is_not_rolled_back(): void
    {
        $this->connection->method('executeStatement')->willThrowException(new \RuntimeException('DB hiba'));
        $this->connection->method('isTransactionActive')->willReturn(false);
        $this->connection->expects($this->never())->method('rollBack');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB hiba');

        $this->reset()->reset();
    }

    public function test_success_preserves_championship_cache(): void
    {
        $statements = [];
        $this->connection->method('executeStatement')->willReturnCallback(function (string $sql) use (&$statements) {
            $statements[] = $sql;

            return 1;
        });

        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->expects($this->never())->method('rollBack');

        $secondLevelCache = $this->createMock(Cache::class);
        $secondLevelCache->expects($this->once())->method('evictEntityRegions');
        $secondLevelCache->expects($this->once())->method('evictCollectionRegions');
        $secondLevelCache->expects($this->once())->method('evictQueryRegions');
        $this->em->expects($this->once())->method('clear');
        $this->em->method('getCache')->willReturn($secondLevelCache);
        $this->resultCachePool->expects($this->once())->method('clear')->willReturn(true);

        $order = [];
        $this->fileCache->method('get')
            ->with(ResultsOfChampionshipController::RESULTS_CACHE_KEY)
            ->willReturnCallback(function () use (&$order) {
                $order[] = 'get';

                return ['x'];
            });
        $this->fileCache->expects($this->once())->method('clearAll')
            ->willReturnCallback(function () use (&$order) {
                $order[] = 'clearAll';
            });
        $this->fileCache->expects($this->once())->method('save')
            ->with(ResultsOfChampionshipController::RESULTS_CACHE_KEY, ['x'])
            ->willReturnCallback(function () use (&$order) {
                $order[] = 'save';
            });

        $counts = $this->reset()->reset();

        $this->assertSame(self::EXPECTED_STATEMENTS, $statements);
        $this->assertSame(['get', 'clearAll', 'save'], $order);
        $this->assertSame(
            [12, 40, 4, 30, 9, 6, 3],
            [$counts->bets, $counts->betAttributes, $counts->results, $counts->resultAttributes,
                $counts->trophies, $counts->alternatives, $counts->users]
        );
    }

    public function test_missing_championship_cache_is_not_written_back(): void
    {
        $this->connection->method('executeStatement')->willReturn(1);
        $this->fileCache->method('get')->willReturn([]);

        $this->fileCache->expects($this->once())->method('clearAll');
        $this->fileCache->expects($this->never())->method('save');

        $this->reset()->reset();
    }

    private function reset(): SeasonReset
    {
        return new SeasonReset($this->em, $this->fileCache, new SeasonFactsProvider($this->em));
    }
}
