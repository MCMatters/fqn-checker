<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\ImportedFunctionsResolver;
use McMatters\FqnChecker\NodeVisitors\UnimportedFunctionsVisitor;
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
    protected $imported = [];

    /**
     * @var array
     */
    protected $unimported = [];

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
    public function getImported(): array
    {
        return array_filter($this->imported, function ($item) {
            return !empty($item);
        });
    }

    /**
     * @return array
     */
    public function getUnimported(): array
    {
        return $this->unimported;
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
        $importedResolverVisitor = new ImportedFunctionsResolver();
        $unimportedVisitor = new UnimportedFunctionsVisitor();

        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($importedResolverVisitor);
        $this->traverser->addVisitor($unimportedVisitor);
        $this->traverser->traverse($this->ast);

        $this->unimported = $unimportedVisitor->getUnimported();
        $this->imported = $unimportedVisitor->getImported();

        return $this;
    }
}
