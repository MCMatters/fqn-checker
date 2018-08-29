<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use const true;

/**
 * Class ImportedConstantsVisitor
 *
 * @package McMatters\FqnChecker\NodeVisitors
 */
class ImportedConstantsVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    protected $imported = [];

    /**
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Use_ && $node->type === Use_::TYPE_CONSTANT) {
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
