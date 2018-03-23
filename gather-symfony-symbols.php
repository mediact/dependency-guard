<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require __DIR__ . '/vendor/autoload.php';

$parserFactory = new ParserFactory();
$parser        = $parserFactory->create(ParserFactory::PREFER_PHP7);
$traverser     = new NodeTraverser();
$tracker       = new class extends NodeVisitorAbstract {
    /** @var bool[] */
    private $symbols = [];

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

        // This class should be added to a configurable blacklist.
        // Otherwise it significantly destroys performance.
        if (strpos($name, 'ParentNotExists') !== false
            || strpos($name, 'TestRepositoryFactory') !== false
            || strpos($name, 'Fixture') !== false
            || strpos($name, 'DbalLogger') !== false
            || strpos($name, 'LazyLoadingValueHolderGenerator') !== false
            || strpos($name, 'AdapterTest') !== false
        ) {
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
     * @return string[]
     */
    public function getSymbols(): array
    {
        return array_keys(array_filter($this->symbols));
    }
};

$traverser->addVisitor($tracker);

/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        __DIR__ . '/vendor/symfony/symfony'
    )
);

foreach ($files as $file) {
    if (!$file->isFile() || !$file->isReadable()) {
        continue;
    }

    try {
        $handle     = $file->openFile('r');
        $contents   = implode('', iterator_to_array($handle));
        $statements = $parser->parse($contents);
    } catch (Error $e) {
        // Either not a PHP file or the broken file should be detected by other
        // tooling entirely.
        continue;
    }

    $traverser->traverse($statements);
}

$symbols = $tracker->getSymbols();

echo json_encode(
    [
        'symbols' => $symbols,
        'count' => count($symbols)
    ],
    JSON_PRETTY_PRINT
) . PHP_EOL;
