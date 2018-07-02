<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command\Exporter;

use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ViolationExporterFactoryInterface
{
    const DEFAULT_FORMAT = 'text';

    /**
     * Create a violation exporter for the given input and output.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ViolationExporterInterface
     */
    public function create(
        InputInterface $input,
        OutputInterface $output
    ): ViolationExporterInterface;

    /**
     * Get a list of output formats.
     *
     * @return string[]
     */
    public function getOutputFormats(): array;
}
