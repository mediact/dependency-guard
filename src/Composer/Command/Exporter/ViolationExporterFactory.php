<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command\Exporter;

use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ViolationExporterFactory implements ViolationExporterFactoryInterface
{
    private const EXPORTERS = [
        'text' => TextViolationExporter::class,
        'json' => JsonViolationExporter::class
    ];

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
    ): ViolationExporterInterface {
        $format = $input->hasOption('format')
            ? $input->getOption('format')
            : static::DEFAULT_FORMAT;

        $class = (
            static::EXPORTERS[$format]
            ?? static::EXPORTERS[static::DEFAULT_FORMAT]
        );

        /** @var ViolationExporterInterface $exporter */
        $exporter = new $class(
            new SymfonyStyle($input, $output)
        );

        return $exporter;
    }

    /**
     * Get a list of output formats.
     *
     * @return string[]
     */
    public function getOutputFormats(): array
    {
        return array_keys(static::EXPORTERS);
    }
}
