<?php

namespace Nicodemuz\DoctrineFixturesTimingBundle\Command;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadFixturesWithTimingCommand extends LoadDataFixturesDoctrineCommand
{
    protected static $defaultName = 'doctrine:fixtures:load-with-timing';

    public function __construct(
        SymfonyFixturesLoader $fixturesLoader,
        ManagerRegistry $doctrine,
        array $purgerFactories = []
    ) {
        parent::__construct($fixturesLoader, $doctrine, $purgerFactories);
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Load data fixtures to your database with timing information')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command extends the default Doctrine fixtures loader
to include timing information for each fixture and a report of the top 15 slowest fixtures.

Example:
  <info>php %command.full_name%</info>
  <info>php %command.full_name% --group=group1 --append</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ui = new SymfonyStyle($input, $output);

        $em = $this->getDoctrine()->getManager($input->getOption('em'));
        if (!$em instanceof EntityManagerInterface) {
            $ui->error('Entity manager could not be retrieved.');
            return 1;
        }

        if (!$input->getOption('append')) {
            if (!$ui->confirm(sprintf('Careful, database "%s" will be purged. Do you want to continue?', $em->getConnection()->getDatabase()), !$input->isInteractive())) {
                return 0;
            }
        }

        $groups = $input->getOption('group');
        $fixtures = $this->fixturesLoader->getFixtures($groups);
        if (!$fixtures) {
            $message = 'Could not find any fixture services to load';
            if (!empty($groups)) {
                $message .= sprintf(' in the groups (%s)', implode(', ', $groups));
            }
            $ui->error($message . '.');
            return 1;
        }

        $purgerFactory = $this->purgerFactories[$input->getOption('purger')] ?? new \Doctrine\Bundle\FixturesBundle\Purger\ORMPurgerFactory();
        $purger = $purgerFactory->createForEntityManager(
            $input->getOption('em'),
            $em,
            $input->getOption('purge-exclusions'),
            $input->getOption('purge-with-truncate')
        );
        $executor = new ORMExecutor($em, $purger);
        $executor->setLogger(function (string $message) use ($ui) {
            $ui->text(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });

        $ui->section('Loading Fixtures');
        $timingResults = [];
        foreach ($fixtures as $fixture) {
            $startTime = microtime(true);
            $executor->execute([$fixture], $input->getOption('append'));
            $endTime = microtime(true);
            $timeTaken = $endTime - $startTime;

            $timingResults[get_class($fixture)] = $timeTaken;
            $ui->writeln(sprintf(
                '<info>Loaded %s in %.3f seconds</info>',
                get_class($fixture),
                $timeTaken
            ));
        }

        arsort($timingResults);
        $top15 = array_slice($timingResults, 0, 15, true);

        $ui->section('Top 15 Slowest Fixtures');
        $ui->table(
            ['Fixture Class', 'Time Taken (seconds)'],
            array_map(
                fn($class, $time) => [$class, sprintf('%.3f', $time)],
                array_keys($top15),
                $top15
            )
        );

        $totalTime = array_sum($timingResults);
        $ui->success(sprintf('All %d fixtures loaded in %.3f seconds', count($fixtures), $totalTime));

        return 0;
    }
}