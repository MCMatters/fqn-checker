<?php

declare(strict_types=1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\ImportedConstantsResolver;
use McMatters\FqnChecker\NodeVisitors\ImportedFunctionsResolver;
use McMatters\FqnChecker\NodeVisitors\NotImportedConstantsVisitor;
use McMatters\FqnChecker\NodeVisitors\NotImportedFunctionsVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

use function array_filter;

class FqnChecker
{
    protected array $functions = [];

    protected array $constants = [];

    /**
     * @var \PhpParser\Node\Stmt[]
     */
    protected array $ast;

    protected NodeTraverser $traverser;

    public function __construct(string $content)
    {
        $this->traverser = new NodeTraverser();

        $this->setAst($content)->resolve();
    }

    public function getImportedFunctions(): array
    {
        return array_filter(
            $this->functions['imported'] ?? [],
            static fn ($item) => !empty($item),
        );
    }

    public function getNotImportedFunctions(): array
    {
        return $this->functions['not_imported'] ?? [];
    }

    public function getImportedConstants(): array
    {
        return array_filter(
            $this->constants['imported'] ?? [],
            static fn ($item) => !empty($item),
        );
    }

    public function getNotImportedConstants(): array
    {
        return $this->constants['not_imported'] ?? [];
    }

    public function getImported(): array
    {
        return [
            'constants' => $this->getImportedConstants(),
            'functions' => $this->getImportedFunctions(),
        ];
    }

    public function getNotImported(): array
    {
        return [
            'constants' => $this->getNotImportedConstants(),
            'functions' => $this->getNotImportedFunctions(),
        ];
    }

    public function all(): array
    {
        return [
            'constants' => [
                'imported' => $this->getImportedConstants(),
                'not_imported' => $this->getNotImportedConstants(),
            ],
            'functions' => [
                'imported' => $this->getImportedFunctions(),
                'not_imported' => $this->getNotImportedFunctions(),
            ],
        ];
    }

    public function getFlattenNotImported(): array
    {
        $notImportedFunctions = $this->getNotImportedFunctions();
        $notImportedConstants = $this->getNotImportedConstants();

        $flatten = [];

        foreach ($notImportedFunctions as $namespace => $functions) {
            $flatten[$namespace]['functions'] = $functions;
        }

        foreach ($notImportedConstants as $namespace => $constants) {
            $flatten[$namespace]['constants'] = $constants;
        }

        return $flatten;
    }

    protected function setAst(string $content): self
    {
        $this->ast = (new ParserFactory())
            ->createForHostVersion()
            ->parse($content);

        return $this;
    }

    protected function resolve(): self
    {
        $this->traverseResolvers();

        $functions = new NotImportedFunctionsVisitor();
        $constants = new NotImportedConstantsVisitor();

        $this->traverser->addVisitor($functions);
        $this->traverser->addVisitor($constants);
        $this->traverser->traverse($this->ast);

        $this->functions = [
            'not_imported' => $functions->getNotImported(),
            'imported' => $functions->getImported(),
        ];

        $this->constants = [
            'not_imported' => $constants->getNotImported(),
            'imported' => $constants->getImported(),
        ];

        return $this;
    }

    protected function traverseResolvers(): void
    {
        $resolvers = [
            new NameResolver(),
            new ImportedConstantsResolver(),
            new ImportedFunctionsResolver(),
        ];

        foreach ($resolvers as $resolver) {
            $this->traverser->addVisitor($resolver);
        }

        $this->traverser->traverse($this->ast);

        foreach ($resolvers as $resolver) {
            $this->traverser->removeVisitor($resolver);
        }
    }
}
