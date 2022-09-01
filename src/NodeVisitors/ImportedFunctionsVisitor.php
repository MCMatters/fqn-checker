<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

use const true;

/**
 * Class ImportedFunctionsVisitor
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class ImportedFunctionsVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    protected $imported = [];

    /**
     * @param \PhpParser\Node $node
     *
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Use_ && $node->type === Use_::TYPE_FUNCTION) {
            foreach ($node->uses as $use) {
                $this->imported[$use->name->toString()] = true;
            }
        }
    }

    /**
     * @return array
     */
    public function getImported(): array
    {
        return $this->imported;
    }
}
