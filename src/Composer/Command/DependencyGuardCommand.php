<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Mediact\DependencyGuard\DependencyGuard;
use Mediact\DependencyGuard\DependencyGuardInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Mediact\DependencyGuard\ViolationIteratorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DependencyGuardCommand extends BaseCommand
{
    public const FORMAT_TEXT = 'text';
    public const FORMAT_JSON = 'json';

    /** @var DependencyGuardInterface */
    private $guard;

    /**
     * Constructor.
     *
     * @param DependencyGuardInterface|null $guard
     */
    public function __construct(DependencyGuardInterface $guard = null)
    {
        $this->guard = $guard ?? new DependencyGuard();
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
                        [
                            static::FORMAT_TEXT,
                            static::FORMAT_JSON
                        ]
                    )
                ),
            static::FORMAT_TEXT
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
        $prompt   = new SymfonyStyle($input, $output);
        $composer = $this->getComposer(true);
        $format   = $input->getOption('format');

        $this->registerAutoloader($composer);
        $violations = $this->guard->determineViolations($composer);

        switch ($format) {
            case static::FORMAT_JSON:
                $this->outputViolationsJson($prompt, $violations);
                break;

            case static::FORMAT_TEXT:
            default:
                $this->outputViolationsText($prompt, $violations);
                break;
        }


        return count($violations) > 0 ? 1 : 0;
    }

    /**
     * Output the violations as JSON.
     *
     * @param SymfonyStyle               $prompt
     * @param ViolationIteratorInterface $violations
     *
     * @return void
     */
    private function outputViolationsJson(
        SymfonyStyle $prompt,
        ViolationIteratorInterface $violations
    ): void {
        $prompt->writeln(
            json_encode(
                $violations,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     * Output the violations as text.
     *
     * @param SymfonyStyle               $prompt
     * @param ViolationIteratorInterface $violations
     *
     * @return void
     */
    private function outputViolationsText(
        SymfonyStyle $prompt,
        ViolationIteratorInterface $violations
    ): void {
        $root = getcwd() . DIRECTORY_SEPARATOR;

        foreach ($violations as $violation) {
            $prompt->error($violation->getMessage());

            $prompt->listing(
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
            $prompt->success('No dependency violations encountered!');

            return;
        }

        $prompt->error(
            sprintf('Number of dependency violations: %d', $numViolations)
        );
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
