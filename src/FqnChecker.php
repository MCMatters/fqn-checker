<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\{
    ImportedConstantsResolver, ImportedFunctionsResolver,
    UnimportedConstantsVisitor, UnimportedFunctionsVisitor
};
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use function array_filter;

/**
 * Class FqnChecker
 *
 * @package McMatters\FqnChecker
 */
class FqnChecker
{
    /**
     * @var array
     */
    protected $functions = [];

    /**
     * @var array
     */
    protected $constants = [];

    /**
     * @var Stmt[]
     */
    protected $ast;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    /**
     * FqnChecker constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->traverser = new NodeTraverser();

        $this->setAst($content)->resolve();
    }

    /**
     * @return array
     */
    public function getImportedFunctions(): array
    {
        return array_filter($this->functions['imported'] ?? [], function ($item) {
            return !empty($item);
        });
    }

    /**
     * @return array
     */
    public function getUnimportedFunctions(): array
    {
        return $this->functions['unimported'] ?? [];
    }

    /**
     * @return array
     */
    public function getImportedConstants(): array
    {
        return array_filter($this->constants['imported'] ?? [], function ($item) {
            return !empty($item);
        });
    }

    /**
     * @return array
     */
    public function getUnimportedConstants(): array
    {
        return $this->constants['unimported'] ?? [];
    }

    /**
     * @return array
     */
    public function getImported(): array
    {
        return [
            'constants' => $this->getImportedConstants(),
            'functions' => $this->getImportedFunctions(),
        ];
    }

    /**
     * @return array
     */
    public function getUnimported(): array
    {
        return [
            'constants' => $this->getUnimportedConstants(),
            'functions' => $this->getUnimportedFunctions(),
        ];
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return [
            'constants' => [
                'imported' => $this->getImportedConstants(),
                'unimported' => $this->getUnimportedConstants(),
            ],
            'functions' => [
                'imported' => $this->getImportedFunctions(),
                'unimported' => $this->getUnimportedFunctions(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFlattenUnimported(): array
    {
        $unimportedFunctions = $this->getUnimportedFunctions();
        $unimportedConstants = $this->getUnimportedConstants();

        $flatten = [];

        foreach ($unimportedFunctions as $namespace => $functions) {
            $flatten[$namespace]['functions'] = $functions;
        }

        foreach ($unimportedConstants as $namespace => $constants) {
            $flatten[$namespace]['constants'] = $constants;
        }

        return $flatten;
    }

    /**
     * @param string $content
     *
     * @return \McMatters\FqnChecker\FqnChecker
     */
    protected function setAst(string $content): self
    {
        $this->ast = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7)
            ->parse($content);

        return $this;
    }

    /**
     * @return \McMatters\FqnChecker\FqnChecker
     */
    protected function resolve(): self
    {
        $this->traverseResolvers();

        $functions = new UnimportedFunctionsVisitor();
        $constants = new UnimportedConstantsVisitor();

        $this->traverser->addVisitor($functions);
        $this->traverser->addVisitor($constants);
        $this->traverser->traverse($this->ast);

        $this->functions = [
            'unimported' => $functions->getUnimported(),
            'imported' => $functions->getImported(),
        ];

        $this->constants = [
            'unimported' => $constants->getUnimported(),
            'imported' => $constants->getImported(),
        ];

        return $this;
    }

    /**
     * @return void
     */
    protected function traverseResolvers()
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
