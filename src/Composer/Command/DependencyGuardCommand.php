<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactory;
use Mediact\DependencyGuard\Composer\Command\Exporter\ViolationExporterFactoryInterface;
use Mediact\DependencyGuard\DependencyGuardFactory;
use Mediact\DependencyGuard\DependencyGuardFactoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DependencyGuardCommand extends BaseCommand
{
    public const EXIT_NO_VIOLATIONS = 0;
    public const EXIT_VIOLATIONS    = 1;

    /** @var DependencyGuardFactoryInterface */
    private $guardFactory;

    /** @var ViolationExporterFactoryInterface */
    private $exporterFactory;

    /**
     * Constructor.
     *
     * @param DependencyGuardFactoryInterface|null   $guardFactory
     * @param ViolationExporterFactoryInterface|null $exporterFactory
     */
    public function __construct(
        DependencyGuardFactoryInterface $guardFactory = null,
        ViolationExporterFactoryInterface $exporterFactory = null
    ) {
        $this->guardFactory    = $guardFactory ?? new DependencyGuardFactory();
        $this->exporterFactory = $exporterFactory ?? new ViolationExporterFactory();

        parent::__construct();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('dependency-guard');
        $this->setDescription(
            'Check Composer dependencies for a --no-dev install.'
        );

        $this->addOption(
            'format',
            'f',
            InputOption::VALUE_REQUIRED,
            'The output format. '
                . implode(
                    ', ',
                    array_map(
                        function (string $format) : string {
                            return sprintf('<comment>%s</comment>', $format);
                        },
                        $this->exporterFactory->getOutputFormats()
                    )
                ),
            ViolationExporterFactoryInterface::DEFAULT_FORMAT
        );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $composer = $this->getComposer(true);
        $guard    = $this->guardFactory->create();

        $this->registerAutoloader($composer);
        $violations = $guard->determineViolations($composer);

        $exporter = $this->exporterFactory->create($input, $output);
        $exporter->export($violations);

        return count($violations) > 0
            ? static::EXIT_VIOLATIONS
            : static::EXIT_NO_VIOLATIONS;
    }

    /**
     * Register the autoloader for the current project, so subject classes can
     * be automatically loaded.
     *
     * @param Composer $composer
     *
     * @return void
     */
    private function registerAutoloader(Composer $composer): void
    {
        $config     = $composer->getConfig();
        $vendor     = $config->get('vendor-dir', 0);
        $autoloader = $vendor . DIRECTORY_SEPARATOR . 'autoload.php';

        if (is_readable($autoloader)) {
            /** @noinspection PhpIncludeInspection */
            require_once $autoloader;
        }
    }
}
