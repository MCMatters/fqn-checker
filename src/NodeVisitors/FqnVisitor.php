<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use const false;

/**
 * Class FqnVisitor
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class FqnVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    protected $functions = [];

    /**
     * @var array
     */
    protected $imported = [];

    /**
     * FqnNodeVisitor constructor.
     *
     * @param array $imported
     */
    public function __construct(array $imported = [])
    {
        $this->imported = $imported;
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($this->shouldSkipNode($node)) {
            return;
        }

        $this->functions[] = [
            'line'     => $node->getLine(),
            'function' => $node->name->toString(),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    protected function shouldSkipNode(Node $node): bool
    {
        return !$this->hasNamespace($node) ||
            !$node instanceof FuncCall ||
            !$node->name instanceof Name ||
            $node->name->isFullyQualified() ||
            isset($this->imported[$node->name->toString()]);
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    protected function hasNamespace(Node $node): bool
    {
        static $classEndsAt = -1;
        static $hasNamespace = false;

        if (!$hasNamespace && $node instanceof Class_) {
            $hasNamespace = $node->namespacedName->isQualified();
            $classEndsAt = $node->getEndLine();
        }

        if ($hasNamespace && $node->getStartLine() >= $classEndsAt) {
            $hasNamespace = false;
            $classEndsAt = -1;
        }

        return $hasNamespace;
    }
}
