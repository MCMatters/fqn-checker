<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use const null;

/**
 * Class UnimportedConstantsVisitor
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class UnimportedConstantsVisitor extends NodeVisitorAbstract
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
     * @param \PhpParser\Node $node
     *
     * @return int|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            if (null === $node->name) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            $this->namespace = $node->name->toString();
            $this->imported[$this->namespace] = $node->getAttribute('imported_constants') ?? [];

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
     * @param \PhpParser\Node $node
     *
     * @return bool
     */
    protected function shouldSkipNode(Node $node): bool
    {
        return !$this->hasNamespace() ||
            !$node instanceof ConstFetch ||
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
