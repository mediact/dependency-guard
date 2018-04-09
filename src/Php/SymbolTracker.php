<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Prodep\Php;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use Throwable;

class SymbolTracker extends NodeVisitorAbstract implements
    SymbolContainerInterface
{
    /** @var bool[] */
    private $symbols = [];

    /** @var string[] */
    private $exclusions = [];

    /**
     * Constructor.
     *
     * @param string[] ...$exclusions
     */
    public function __construct(string ...$exclusions)
    {
        $this->exclusions = $exclusions;
    }

    /**
     * @param Node $node
     *
     * @return void
     */
    public function enterNode(Node $node): void
    {
        $name = null;

        if ($node instanceof Name) {
            $name = $node->toString();
        }

        if ($name === null || array_key_exists($name, $this->symbols)) {
            return;
        }

        if ($this->isExcluded($name)) {
            $this->symbols[$name] = false;
            return;
        }

        if (!class_exists($name)) {
            $this->symbols[$name] = false;
            return;
        }

        try {
            $reflection = new ReflectionClass($name);
        } catch (Throwable $e) {
            $this->symbols[$name] = false;
            return;
        }

        if ($reflection->isInternal()) {
            $this->symbols[$name] = false;
            return;
        }

        $this->symbols[$name] = true;
    }

    /**
     * Check whether the given symbol is excluded.
     *
     * @param string $symbol
     *
     * @return bool
     */
    private function isExcluded(string $symbol): bool
    {
        return array_reduce(
            $this->exclusions,
            function (bool $carry, string $exclusion) use ($symbol) : bool {
                return (
                    $carry
                    || $exclusion === $symbol
                    || fnmatch($exclusion, $symbol, FNM_PATHNAME | FNM_NOESCAPE)
                    || (
                        strpos($symbol, $exclusion) === 0
                        && preg_match('#\\\\$#', $exclusion)
                    )
                );
            },
            false
        );
    }

    /**
     * Get the symbols that are present in the tracker.
     *
     * @return iterable|string[]
     */
    public function getSymbols(): iterable
    {
        return array_keys(array_filter($this->symbols));
    }
}
