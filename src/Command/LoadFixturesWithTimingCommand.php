<?php

declare(strict_types=1);

namespace Nicodemuz\DoctrineFixturesTimingBundle\Command;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadFixturesWithTimingCommand extends Command
{
    public function __construct(
        private readonly SymfonyFixturesLoader $fixturesLoader,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Load fixtures and report timing information, including the top 15 slowest fixtures');
        $this->setName('nicodemuz:doctrine:fixtures:load-with-timing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fixtures = $this->fixturesLoader->getFixtures();
        $executor = new ORMExecutor($this->entityManager);

        // Array to store timing results
        $timingResults = [];

        $io->section('Loading Fixtures');
        foreach ($fixtures as $fixture) {
            $startTime = microtime(true); // Start timing

            // Load the fixture
            $executor->execute([$fixture], true); // true for TRUNCATE

            $endTime = microtime(true); // End timing
            $timeTaken = $endTime - $startTime;

            // Store the result
            $timingResults[get_class($fixture)] = $timeTaken;

            // Output progress
            $io->writeln(sprintf(
                '<info>Loaded %s in %.3f seconds</info>',
                get_class($fixture),
                $timeTaken
            ));
        }

        // Sort fixtures by time taken (descending)
        arsort($timingResults);

        // Generate top 15 report
        $io->section('Top 15 Slowest Fixtures');
        $top15 = array_slice($timingResults, 0, 15, true); // Preserve keys
        $io->table(
            ['Fixture Class', 'Time Taken (seconds)'],
            array_map(
                fn($class, $time) => [$class, sprintf('%.3f', $time)],
                array_keys($top15),
                $top15
            )
        );

        $totalTime = array_sum($timingResults);
        $io->success(sprintf('All %d fixtures loaded in %.3f seconds', count($fixtures), $totalTime));

        return Command::SUCCESS;
    }
}
