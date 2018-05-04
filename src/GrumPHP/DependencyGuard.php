<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\GrumPHP;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\BufferIO;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use Mediact\DependencyGuard\DependencyGuard as Guard;
use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DependencyGuard implements TaskInterface
{
    /** @var Composer */
    private $composer;

    /** @var ViolationExporterInterface */
    private $exporter;

    /**
     * Constructor.
     *
     * @param ViolationExporterInterface $exporter
     */
    public function __construct(ViolationExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'dependency-guard';
    }

    /**
     * Get the configurable options for the dependency guard.
     *
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return (
            $context instanceof GitPreCommitContext
            || $context instanceof RunContext
        );
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        foreach (['composer.lock', 'composer.json'] as $file) {
            if (!is_readable(getcwd() . DIRECTORY_SEPARATOR . $file)) {
                return TaskResult::createSkipped($this, $context);
            }
        }

        $composer   = $this->getComposer();
        $guard      = new Guard();
        $violations = $guard->determineViolations($composer);

        if (count($violations)) {
            $this->exporter->export($violations);

            return TaskResult::createFailed(
                $this,
                $context,
                'Encountered dependency violations.'
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * Get the composer instance.
     *
     * @return Composer
     */
    private function getComposer(): Composer
    {
        if ($this->composer === null) {
            $this->composer = Factory::create(new BufferIO());
        }

        return $this->composer;
    }

    /**
     * Get the configuration.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return [];
    }
}
