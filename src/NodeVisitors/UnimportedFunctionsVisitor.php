<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use const null;

/**
 * Class UnimportedFunctionsVisitor
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class UnimportedFunctionsVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    protected $unimported = [];

    /**
     * @var array
     */
    protected $imported = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name->toString();
            $this->imported[$this->namespace] = $node->getAttribute('imported_functions');

            return;
        }

        if ($this->shouldSkipNode($node)) {
            return;
        }

        $this->unimported[$this->namespace][$node->name->toString()][] = $node->getLine();
    }

    /**
     * @return array
     */
    public function getUnimported(): array
    {
        return $this->unimported;
    }

    /**
     * @return array
     */
    public function getImported(): array
    {
        return $this->imported;
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    protected function shouldSkipNode(Node $node): bool
    {
        return !$this->hasNamespace() ||
            !$node instanceof FuncCall ||
            !$node->name instanceof Name ||
            $node->name->isFullyQualified() ||
            isset($this->imported[$this->namespace][$node->name->toString()]);
    }

    /**
     * @return bool
     */
    protected function hasNamespace(): bool
    {
        return null !== $this->namespace;
    }
}
