<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Mediact\DependencyGuard\Iterator\FileIteratorInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterInterface;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class SymbolExtractor implements SymbolExtractorInterface
{
    /** @var Parser */
    private $parser;

    /**
     * Constructor.
     *
     * @param Parser|null $parser
     */
    public function __construct(Parser $parser = null)
    {
        if ($parser === null) {
            $factory = new ParserFactory();
            $parser  = $factory->create(ParserFactory::PREFER_PHP7);
        }

        $this->parser = $parser;
    }

    /**
     * Extract the PHP symbols from the given files.
     *
     * @param FileIteratorInterface $files
     * @param SymbolFilterInterface $filter
     *
     * @return SymbolIteratorInterface
     */
    public function extract(
        FileIteratorInterface $files,
        SymbolFilterInterface $filter
    ): SymbolIteratorInterface {
        $symbols = [];

        foreach ($files as $file) {
            try {
                $handle = $file->openFile('rb');
            } catch (\RuntimeException $e) {
                /*
                 * File cannot be opened, e.g. not a file, not readable etc.
                 */
                continue;
            }

            $fileSize = $file->getSize();

            if (0 === $fileSize) {
                continue;
            }

            $contents = $handle->fread($fileSize);

            if (!$contents) { // phpstan thinks this can only return string.
                continue;
            }

            try {
                $statements = $this->parser->parse($contents);
            } catch (Error $e) {
                // Either not a file, file is not readable, not a PHP file or
                // the broken file should be detected by other tooling entirely.
                continue;
            }

            $tracker   = new SymbolTracker($filter);
            $traverser = new NodeTraverser();
            $traverser->addVisitor($tracker);
            $traverser->traverse($statements);

            foreach ($tracker->getSymbols() as $node) {
                $symbols[] = new Symbol($file, $node);
            }
        }

        return new SymbolIterator(...$symbols);
    }
}
