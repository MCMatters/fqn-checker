<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ImportedConstantsResolver
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class ImportedConstantsResolver extends NodeVisitorAbstract
{
    /**
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_ && !empty($node->stmts)) {
            $traverser = new NodeTraverser();
            $visitor = new ImportedConstantsVisitor();
            $traverser->addVisitor($visitor);
            $traverser->traverse($node->stmts);

            $node->setAttribute('imported_constants', $visitor->getImported());
        }
    }
}
