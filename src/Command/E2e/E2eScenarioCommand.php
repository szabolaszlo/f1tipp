<?php

namespace App\Command\E2e;

use App\E2e\E2eFixtures;
use App\E2e\E2eScenarios;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * E2E: reset, majd egy nevesített szenárió felépítése. Az utolsó stdout sor JSON
 * (serverNow, események id-je és határideje, rögzített eredmények id-je) – ezt olvassa a CodeceptJS helper.
 */
class E2eScenarioCommand extends Command
{
    protected static $defaultName = 'app:e2e:scenario';

    private E2eFixtures $fixtures;
    private E2eScenarios $scenarios;
    private string $kernelEnv;

    public function __construct(E2eFixtures $fixtures, E2eScenarios $scenarios, string $kernelEnv)
    {
        parent::__construct();
        $this->fixtures = $fixtures;
        $this->scenarios = $scenarios;
        $this->kernelEnv = $kernelEnv;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('E2E only: reset the e2e database and build a named scenario')
            ->addArgument('name', InputArgument::REQUIRED, implode(', ', E2eScenarios::NAMES))
            ->addOption('deadline-in', null, InputOption::VALUE_REQUIRED, 'about-to-close: seconds until the qualify deadline', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');

        try {
            E2eGuard::assertE2e($this->kernelEnv);
            $this->fixtures->assertSafe();
            if (!in_array($name, E2eScenarios::NAMES, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown scenario "%s". Valid: %s',
                    $name,
                    implode(', ', E2eScenarios::NAMES)
                ));
            }
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }

        E2eResetCommand::reset($this, $this->fixtures);
        $info = $this->scenarios->build($name, (int) $input->getOption('deadline-in'));
        $this->fixtures->clearCaches();

        $output->writeln(json_encode($info));

        return Command::SUCCESS;
    }
}
