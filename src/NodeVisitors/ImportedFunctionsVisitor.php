<?php

declare(strict_types=1);

namespace McMatters\FqnChecker\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

use const true;

class ImportedFunctionsVisitor extends NodeVisitorAbstract
{
    protected array $imported = [];

    public function enterNode(Node $node): void
    {
        if ($node instanceof Use_ && $node->type === Use_::TYPE_FUNCTION) {
            foreach ($node->uses as $use) {
                $this->imported[$use->name->toString()] = true;
            }
        }
    }

    public function getImported(): array
    {
        return $this->imported;
    }
}
