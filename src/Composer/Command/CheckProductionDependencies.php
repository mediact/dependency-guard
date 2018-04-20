<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Command;

use Composer\Command\BaseCommand;
use Mediact\DependencyGuard\DependencyGuard;
use Mediact\DependencyGuard\DependencyGuardInterface;
use Mediact\DependencyGuard\Php\SymbolInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckProductionDependencies extends BaseCommand
{
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
        $this->setName('check-production-deps');
        $this->setDescription(
            'Check Composer dependencies for a --no-dev install.'
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
        $root       = getcwd() . DIRECTORY_SEPARATOR;
        $prompt     = new SymfonyStyle($input, $output);
        $violations = $this->guard->determineViolations(
            $this->getComposer(true)
        );

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
            return 0;
        }

        $prompt->error(
            sprintf('Number of dependency violations: %d', $numViolations)
        );

        return 1;
    }
}
