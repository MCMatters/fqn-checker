<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\FqnVisitor;
use McMatters\FqnChecker\NodeVisitors\ImportedFunctionsVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * Class FqnChecker
 *
 * @package McMatters\FqnChecker
 */
class FqnChecker
{
    /**
     * @param string $content
     *
     * @return array
     */
    public static function check(string $content): array
    {
        $ast = (new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7)
            ->parse($content);

        return self::traverse($ast);
    }

    /**
     * @param Node[] $ast
     *
     * @return array
     */
    protected static function traverse(array $ast): array
    {
        $traverser = new NodeTraverser();

        $importedVisitor = new ImportedFunctionsVisitor();
        $nameResolverVisitor = new NameResolver();

        $traverser->addVisitor($nameResolverVisitor);
        $traverser->addVisitor($importedVisitor);
        $traverser->traverse($ast);

        $fqnVisitor = new FqnVisitor($importedVisitor->getImported());

        $traverser->removeVisitor($nameResolverVisitor);
        $traverser->removeVisitor($importedVisitor);
        $traverser->addVisitor($fqnVisitor);
        $traverser->traverse($ast);

        return $fqnVisitor->getFunctions();
    }
}
