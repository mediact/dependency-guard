<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command\Exporter;

use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TextViolationExporter implements ViolationExporterInterface
{
    /** @var SymfonyStyle */
    private $prompt;

    /**
     * Constructor.
     *
     * @param SymfonyStyle $prompt
     */
    public function __construct(SymfonyStyle $prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * Export the given violations.
     *
     * @param ViolationIteratorInterface $violations
     *
     * @return void
     */
    public function export(ViolationIteratorInterface $violations): void
    {
        $root = getcwd() . DIRECTORY_SEPARATOR;

        foreach ($violations as $violation) {
            $this->prompt->error($violation->getMessage());

            $this->prompt->listing(
                array_map(
                    function (
                        SymbolInterface $symbol
                    ) use (
                        $root
                    ) : string {
                        return sprintf(
                            'Detected <comment>%s</comment> '
                            . 'in <comment>%s:%d</comment>',
                            $symbol->getName(),
                            preg_replace(
                                sprintf('#^%s#', $root),
                                '',
                                $symbol->getFile()
                            ),
                            $symbol->getLine()
                        );
                    },
                    iterator_to_array($violation->getSymbols())
                )
            );
        }

        $numViolations = count($violations);

        if ($numViolations === 0) {
            $this->prompt->success('No dependency violations encountered!');

            return;
        }

        $this->prompt->error(
            sprintf('Number of dependency violations: %d', $numViolations)
        );
    }
}
