<?php

declare(strict_types = 1);

namespace McMatters\FqnChecker\Tokenizers;

use const false, null, true, T_FUNCTION, T_STRING, T_USE;
use function array_slice, is_array, is_string, token_get_all;

/**
 * Class FunctionTokenizer
 *
 * @package McMatters\FqnChecker\Tokenizers
 */
class FunctionTokenizer
{
    /**
     * @var array
     */
    protected $tokens;

    /**
     * ImportTokenizer constructor.
     *
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->tokens = token_get_all($content);
    }

    /**
     * @return array
     */
    public function getAllImportedFunctions(): array
    {
        $functions = [];

        $startWatching = false;

        foreach ($this->tokens as $key => $token) {
            $isArrayToken = is_array($token);

            if (!($isArrayToken && $token[0] === T_USE)) {
                if ($startWatching) {
                    if ($this->isTokenSemicolon($token)) {
                        $startWatching = false;
                    } elseif ($isArrayToken && $token['0'] === T_STRING) {
                        $functions[$token[1]] = true;
                    }
                }

                continue;
            }

            $startWatching = $this->isFunctionImported($key);
        }

        return $functions;
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    protected function isFunctionImported(int $offset): bool
    {
        foreach (array_slice($this->tokens, $offset + 1, null, true) as $token) {
            if ($this->isTokenSemicolon($token)) {
                return false;
            }

            if (is_array($token) && $token[0] === T_FUNCTION) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array|string $token
     *
     * @return bool
     */
    protected function isTokenSemicolon($token): bool
    {
        return is_string($token) && $token === ';';
    }
}
