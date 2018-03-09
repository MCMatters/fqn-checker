<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker;

use McMatters\FqnChecker\NodeVisitors\FqnVisitor;
use McMatters\FqnChecker\Tokenizers\FunctionTokenizer;
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

        $imported = (new FunctionTokenizer($content))->getAllImportedFunctions();

        return self::traverse($ast, $imported);
    }

    /**
     * @param Node[] $ast
     * @param array $imported
     *
     * @return array
     */
    protected static function traverse(array $ast, array $imported = []): array
    {
        $visitor = new FqnVisitor($imported);
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->getFunctions();
    }
}
