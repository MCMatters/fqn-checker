<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ImportedFunctionsResolver
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class ImportedFunctionsResolver extends NodeVisitorAbstract
{
    /**
     * @param \PhpParser\Node $node
     *
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

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }
}
