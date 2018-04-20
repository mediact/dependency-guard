<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Php;

use Mediact\DependencyGuard\Iterator\FileIteratorInterface;
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
     * @param string[]              ...$exclusions
     *
     * @return SymbolIteratorInterface
     */
    public function extract(
        FileIteratorInterface $files,
        string ...$exclusions
    ): SymbolIteratorInterface {
        $symbols = [];

        foreach ($files as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            try {
                $handle     = $file->openFile('r');
                $contents   = implode('', iterator_to_array($handle));
                $statements = $this->parser->parse($contents);
            } catch (Error $e) {
                // Either not a PHP file or the broken file should be detected by other
                // tooling entirely.
                continue;
            }

            $tracker   = new SymbolTracker(...$exclusions);
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
