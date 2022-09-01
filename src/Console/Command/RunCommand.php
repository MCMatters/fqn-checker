<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\Console\Command;

use McMatters\FqnChecker\FqnChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function count;
use function implode;
use function is_file;
use function ucfirst;

use const PHP_EOL;

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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!count($files = $this->getFiles($input))) {
            $output->writeln('There are no php files in your directory');

            return 0;
        }

        foreach ($files as $file) {
            $this->renderTable(
                $output,
                $file,
                (new FqnChecker($file->getContents()))->getFlattenUnimported()
            );
        }

	    return 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return array|\Symfony\Component\Finder\Finder
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function getFiles(InputInterface $input)
    {
        $path = $input->getArgument('path');

        return is_file($path)
            ? [new SplFileInfo($path, $path, $path)]
            : (new Finder())->files()->in($path)->name('*.php');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string|\Symfony\Component\Finder\SplFileInfo $file
     * @param array $flatten
     *
     * @return void
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function renderTable(
        OutputInterface $output,
        $file,
        array $flatten
    ) {
        $rows = [];

        foreach ($flatten as $namespace => $types) {
            $output->writeln([
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

            $output->write(PHP_EOL);
        }
    }
}
