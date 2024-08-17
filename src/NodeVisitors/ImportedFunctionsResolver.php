<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

class ImportedFunctionsResolver extends NodeVisitorAbstract
{
    /**
     * @return int|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_ && !empty($node->stmts)) {
            $traverser = new NodeTraverser();
            $visitor = new ImportedFunctionsVisitor();

            $traverser->addVisitor($visitor);
            $traverser->traverse($node->stmts);

            $node->setAttribute('imported_functions', $visitor->getImported());

            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }
    }
}
