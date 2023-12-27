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

use function implode;
use function is_file;
use function ucfirst;

use const PHP_EOL;

class RunCommand extends Command
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($files = $this->getFiles($input))) {
            $output->writeln('There are no php files in your directory');

            return 0;
        }

        foreach ($files as $file) {
            $this->renderTable(
                $output,
                $file,
                (new FqnChecker($file->getContents()))->getFlattenNotImported(),
            );
        }

	    return 0;
    }

    protected function getFiles(InputInterface $input): array|Finder
    {
        $path = $input->getArgument('path');

        return is_file($path)
            ? [new SplFileInfo($path, $path, $path)]
            : (new Finder())->files()->in($path)->name('*.php');
    }

    protected function renderTable(
        OutputInterface $output,
        SplFileInfo|string $file,
        array $flatten,
    ): void {
        $rows = [];

        foreach ($flatten as $namespace => $types) {
            $output->writeln([
                "FILE: {$file}",
                "NAMESPACE: {$namespace}",
            ]);

            $table = new Table($output);
            $table->setHeaders(['Not imported', 'Lines']);

            foreach ($types as $type => $notImported) {
                $rows[] = new TableSeparator();
                $rows[] = [new TableCell(
                    '<comment>'.ucfirst($type).'</comment>',
                    ['colspan' => 2]
                )];
                $rows[] = new TableSeparator();

                foreach ($notImported as $key => $values) {
                    $rows[] = [$key, implode(', ', $values)];
                }
            }

            $table->setRows($rows)->render();

            $output->write(PHP_EOL);
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('fqn-checker:check')
            ->setDefinition(new InputDefinition([new InputArgument('path')]));
    }
}
