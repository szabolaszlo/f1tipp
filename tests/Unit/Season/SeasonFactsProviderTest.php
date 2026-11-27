<?php

namespace App\Tests\Unit\Season;

use App\Season\SeasonFactsProvider;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SeasonFactsProviderTest extends TestCase
{
    public function test_lists_the_non_innodb_tables_among_the_reset_tables(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchOne')->willReturn(false);
        $connection->method('fetchAssociative')->willReturn([
            'bets' => '1', 'bet_attributes' => '1', 'results' => '0', 'result_attributes' => '0',
            'trophies' => '0', 'alternatives' => '0', 'users' => '3',
        ]);

        $engineQuery = null;
        $connection->expects($this->once())->method('fetchFirstColumn')
            ->willReturnCallback(function (string $sql, array $params) use (&$engineQuery) {
                $engineQuery = [$sql, $params];

                return ['bet', 'trophy'];
            });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $facts = (new SeasonFactsProvider($em))->load(new \DateTimeImmutable('2027-02-01'));

        $this->assertSame(['bet', 'trophy'], $facts->nonTransactionalTables);
        [$sql, $params] = $engineQuery;
        $this->assertStringContainsString('information_schema.TABLES', $sql);
        $this->assertStringContainsString('TABLE_SCHEMA = DATABASE()', $sql);
        $this->assertStringContainsString("ENGINE <> 'InnoDB'", $sql);
        $this->assertSame(
            ['bet', 'bet_attribute', 'result', 'result_attribute', 'alternative_championship', 'trophy', 'user'],
            $params
        );
    }
}
