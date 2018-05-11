<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Mediact\DependencyGuard\Iterator\FileIteratorFactoryInterface;
use Mediact\DependencyGuard\Php\Filter\SymbolFilterFactoryInterface;
use Mediact\DependencyGuard\Php\SymbolExtractorInterface;
use Mediact\DependencyGuard\Violation\Filter\ViolationFilterFactoryInterface;
use Mediact\DependencyGuard\Violation\Finder\ViolationFinderInterface;
use Mediact\DependencyGuard\Violation\ViolationIterator;
use Mediact\DependencyGuard\Violation\ViolationIteratorInterface;

class DependencyGuard implements DependencyGuardInterface
{
    /** @var FileIteratorFactoryInterface */
    private $sourceFileFactory;

    /** @var SymbolExtractorInterface */
    private $extractor;

    /** @var SymbolFilterFactoryInterface */
    private $symbolFilterFactory;

    /** @var ViolationFinderInterface */
    private $finder;

    /** @var ViolationFilterFactoryInterface */
    private $violationFilterFactory;

    /**
     * Constructor.
     *
     * @param FileIteratorFactoryInterface    $sourceFileFactory
     * @param SymbolExtractorInterface        $extractor
     * @param SymbolFilterFactoryInterface    $symbolFilterFactory
     * @param ViolationFinderInterface        $finder
     * @param ViolationFilterFactoryInterface $resultFilterFactory
     */
    public function __construct(
        FileIteratorFactoryInterface $sourceFileFactory,
        SymbolExtractorInterface $extractor,
        SymbolFilterFactoryInterface $symbolFilterFactory,
        ViolationFinderInterface $finder,
        ViolationFilterFactoryInterface $resultFilterFactory
    ) {
        $this->sourceFileFactory      = $sourceFileFactory;
        $this->extractor              = $extractor;
        $this->symbolFilterFactory    = $symbolFilterFactory;
        $this->finder                 = $finder;
        $this->violationFilterFactory = $resultFilterFactory;
    }

    /**
     * Determine dependency violations for the given Composer instance.
     *
     * @param Composer $composer
     *
     * @return ViolationIteratorInterface
     */
    public function determineViolations(Composer $composer): ViolationIteratorInterface
    {
        $files        = $this->sourceFileFactory->create($composer);
        $symbolFilter = $this->symbolFilterFactory->create($composer);
        $symbols      = $this->extractor->extract($files, $symbolFilter);
        $violations   = $this->finder->find($composer, $symbols);

        return new ViolationIterator(
            ...array_filter(
                iterator_to_array($violations),
                $this->violationFilterFactory->create($composer)
            )
        );
    }
}
