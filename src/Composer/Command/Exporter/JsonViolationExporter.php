<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command\Exporter;

use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JsonViolationExporter implements ViolationExporterInterface
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
        $this->prompt->writeln(
            json_encode(
                $violations,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }
}
