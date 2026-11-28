<?php

namespace App\Command\E2e;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * E2E: az adatbázis állapotának pillanatképe egyetlen JSON sorban (táblák sorszáma, felhasználók pontjai,
 * pilóták pontja és státusza, események időpontja). A CodeceptJS "az adatbázis nem változott" lépése
 * két ilyen pillanatképet hasonlít össze.
 */
#[AsCommand(name: 'app:e2e:counts')]
class E2eCountsCommand extends Command
{
    private const TABLES = [
        'bet', 'bet_attribute', 'result', 'result_attribute', 'alternative_championship', 'trophy',
        'event', 'user', 'driver', 'championship_point', 'prize_draw', 'prize', 'information',
        'setting', 'message_wall',
    ];

    private Connection $connection;
    private string $kernelEnv;

    public function __construct(Connection $connection, string $kernelEnv)
    {
        parent::__construct();
        $this->connection = $connection;
        $this->kernelEnv = $kernelEnv;
    }

    protected function configure(): void
    {
        $this->setDescription('E2E only: print row counts, user/driver points and event dates of the e2e database as one JSON line');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            E2eGuard::assertE2e($this->kernelEnv);
            E2eGuard::assertE2eDatabase($this->connection->getParams()['dbname'] ?? null);
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        $nullableInt = static fn ($value) => $value === null ? null : (int) $value;

        $tables = [];
        foreach (self::TABLES as $table) {
            $tables[$table] = (int) $this->connection->fetchOne(sprintf('SELECT COUNT(*) FROM `%s`', $table));
        }

        $users = [];
        foreach ($this->connection->fetchAllAssociative(
            'SELECT username, point_summary, point_difference, alternative_point_summary, alternative_point_difference, is_alter_champs FROM `user` ORDER BY id'
        ) as $row) {
            $users[$row['username']] = [
                'point_summary' => (int) $row['point_summary'],
                'point_difference' => $nullableInt($row['point_difference']),
                'alternative_point_summary' => $nullableInt($row['alternative_point_summary']),
                'alternative_point_difference' => $nullableInt($row['alternative_point_difference']),
                'is_alter_champs' => $row['is_alter_champs'] === null ? null : (bool) $row['is_alter_champs'],
            ];
        }

        $drivers = [];
        foreach ($this->connection->fetchAllAssociative('SELECT short, point, status FROM `driver` ORDER BY id') as $row) {
            $drivers[$row['short']] = [
                'point' => (int) $row['point'],
                'status' => $row['status'] === null ? null : (bool) $row['status'],
            ];
        }

        $events = [];
        foreach ($this->connection->fetchAllAssociative('SELECT id, date_time FROM `event` ORDER BY id') as $row) {
            $events[(string) $row['id']] = $row['date_time'];
        }

        $output->writeln(json_encode([
            'tables' => $tables,
            'users' => $users,
            'drivers' => $drivers,
            'events' => $events,
        ]));

        return Command::SUCCESS;
    }
}
