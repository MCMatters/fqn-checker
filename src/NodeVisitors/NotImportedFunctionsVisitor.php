<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

use const null;

class NotImportedFunctionsVisitor extends NodeVisitorAbstract
{
    protected array $notImported = [];

    protected array $imported = [];

    protected ?string $namespace = null;

    /**
     * @return int|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            if (null === $node->name) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            $this->namespace = $node->name->toString();
            $this->imported[$this->namespace] = $node->getAttribute('imported_functions') ?? [];

            return;
        }

        if ($this->shouldSkipNode($node)) {
            return;
        }

        $this->notImported[$this->namespace][$node->name->toString()][] = $node->getLine();
    }

    public function getNotImported(): array
    {
        return $this->notImported;
    }

    public function getImported(): array
    {
        return $this->imported;
    }

    protected function shouldSkipNode(Node $node): bool
    {
        return !$this->hasNamespace() ||
            !$node instanceof FuncCall ||
            !$node->name instanceof Name ||
            $node->name->isFullyQualified() ||
            isset($this->imported[$this->namespace][$node->name->toString()]);
    }

    protected function hasNamespace(): bool
    {
        return null !== $this->namespace;
    }
}
