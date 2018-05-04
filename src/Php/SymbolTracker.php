<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use Throwable;

class SymbolTracker extends NodeVisitorAbstract implements
    SymbolTrackerInterface
{
    /** @var bool[]|Name[][] */
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
     * Track the given node.
     *
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

        if ($name === null
            || (array_key_exists($name, $this->symbols)
                && $this->symbols[$name] === false
            )
        ) {
            return;
        }

        if ($this->isExcluded($name) || !$this->isValidClass($name)) {
            $this->symbols[$name] = false;
            return;
        }

        if (!array_key_exists($name, $this->symbols)) {
            $this->symbols[$name] = [];
        }

        $this->symbols[$name][] = $node;
    }

    /**
     * Check whether the given symbol name is a valid class name.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isValidClass(string $name): bool
    {
        if (!class_exists($name)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($name);
        } catch (Throwable $e) {
            return false;
        }

        return !$reflection->isInternal();
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
     * @return iterable|Name[][]
     */
    public function getSymbols(): iterable
    {
        return array_reduce(
            array_filter($this->symbols),
            function (array $carry, array $names) : array {
                foreach ($names as $name) {
                    $carry[] = $name;
                }

                return $carry;
            },
            []
        );
    }
}
