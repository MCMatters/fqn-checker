<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\ImportedFunctionsVisitor;
use McMatters\FqnChecker\NodeVisitors\UnimportedFunctionsVisitor;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

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

        $this->setAst($content)
            ->setImported()
            ->setUnimported();
    }

    /**
     * @return array
     */
    public function getImported(): array
    {
        return $this->imported;
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
    protected function setImported(): self
    {
        $importedVisitor = new ImportedFunctionsVisitor();
        $nameResolverVisitor = new NameResolver();

        $this->traverser->addVisitor($nameResolverVisitor);
        $this->traverser->addVisitor($importedVisitor);
        $this->traverser->traverse($this->ast);
        $this->traverser->removeVisitor($nameResolverVisitor);
        $this->traverser->removeVisitor($importedVisitor);

        $this->imported = $importedVisitor->getImported();

        return $this;
    }

    /**
     * @return \McMatters\FqnChecker\FqnChecker
     */
    protected function setUnimported(): self
    {
        $unimportedVisitor = new UnimportedFunctionsVisitor($this->getImported());

        $this->traverser->addVisitor($unimportedVisitor);
        $this->traverser->traverse($this->ast);
        $this->traverser->removeVisitor($unimportedVisitor);

        $this->unimported = $unimportedVisitor->getUnimported();

        return $this;
    }
}
