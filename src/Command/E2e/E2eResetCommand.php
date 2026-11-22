<?php

namespace App\Command\E2e;

use App\E2e\E2eFixtures;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * E2E adatbázis nulláról: létrehozás (ha kell), séma újraépítése, alapadatok, cache ürítés.
 */
#[AsCommand(name: 'app:e2e:reset')]
class E2eResetCommand extends Command
{

    private E2eFixtures $fixtures;
    private string $kernelEnv;

    public function __construct(E2eFixtures $fixtures, string $kernelEnv)
    {
        parent::__construct();
        $this->fixtures = $fixtures;
        $this->kernelEnv = $kernelEnv;
    }

    protected function configure(): void
    {
        $this->setDescription('E2E only: recreate the e2e database with base fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            E2eGuard::assertE2e($this->kernelEnv);
            $this->fixtures->assertSafe();
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        self::reset($this, $this->fixtures);
        $output->writeln(json_encode(['ok' => true]));

        return Command::SUCCESS;
    }

    /**
     * Közös a scenario paranccsal: adatbázis létrehozása, ha nincs, séma + alapadatok, cache ürítés.
     */
    public static function reset(Command $command, E2eFixtures $fixtures): void
    {
        // a database:create előtt is: rossz DATABASE_URL esetén ne hozzunk létre / töröljünk semmit
        $fixtures->assertSafe();

        $command->getApplication()->find('doctrine:database:create')
            ->run(new ArrayInput(['--if-not-exists' => true]), new NullOutput());

        $fixtures->rebuild();
        $fixtures->clearCaches();
    }
}
