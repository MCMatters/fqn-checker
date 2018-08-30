<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\Console\Command;

use McMatters\FqnChecker\FqnChecker;
use Symfony\Component\Console\{
    Command\Command, Helper\Table, Helper\TableCell, Helper\TableSeparator,
    Input\InputArgument, Input\InputDefinition, Input\InputInterface, Output\OutputInterface
};
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use const PHP_EOL;
use function count, implode, is_file, ucfirst;

/**
 * Class RunCommand
 *
 * @package McMatters\FqnChecker\Console\Command
 */
class RunCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('fqn-checker:check')->setDefinition(new InputDefinition([
            new InputArgument('path'),
        ]));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        if (is_file($path)) {
            $files = [new SplFileInfo($path, $path, $path)];
        } else {
            $files = (new Finder())->files()->in($path)->name('*.php');
        }

        if (!count($files)) {
            $output->writeln('There are no php files in your directory');

            return;
        }

        foreach ($files as $file) {
            $checker = new FqnChecker($file->getContents());
            $flatten = $checker->getFlattenUnimported();
            $rows = [];

            foreach ($flatten as $namespace => $types) {
                $output->writeln([
                    PHP_EOL,
                    "FILE: {$file}",
                    "NAMESPACE: {$namespace}",
                ]);

                $table = new Table($output);
                $table->setHeaders(['Unimported', 'Lines']);

                foreach ($types as $type => $unimported) {
                    $rows[] = new TableSeparator();
                    $rows[] = [new TableCell(
                        '<comment>'.ucfirst($type).'</comment>',
                        ['colspan' => 2]
                    )];
                    $rows[] = new TableSeparator();

                    foreach ($unimported as $key => $values) {
                        $rows[] = [$key, implode(', ', $values)];
                    }
                }

                $table->setRows($rows)->render();
            }
        }
    }
}
