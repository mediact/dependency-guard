<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use Composer\Composer;
use Mediact\DependencyGuard\Composer\Iterator\SourceFileIteratorFactory;
use Mediact\DependencyGuard\Iterator\FileIteratorFactoryInterface;
use Mediact\DependencyGuard\Php\SymbolExtractor;
use Mediact\DependencyGuard\Php\SymbolExtractorInterface;

class DependencyGuard implements DependencyGuardInterface
{
    /** @var FileIteratorFactoryInterface */
    private $sourceFileFactory;

    /** @var SymbolExtractorInterface */
    private $extractor;

    /** @var ViolationFinderInterface|null */
    private $finder;

    /**
     * Constructor.
     *
     * @param FileIteratorFactoryInterface|null $sourceFileFactory
     * @param SymbolExtractorInterface|null     $extractor
     * @param ViolationFinderInterface|null     $finder
     */
    public function __construct(
        FileIteratorFactoryInterface $sourceFileFactory = null,
        SymbolExtractorInterface $extractor = null,
        ViolationFinderInterface $finder = null
    ) {
        $this->sourceFileFactory = (
            $sourceFileFactory ?? new SourceFileIteratorFactory()
        );
        $this->extractor         = $extractor ?? new SymbolExtractor();
        $this->finder            = $finder ?? new ViolationFinder();
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
        $files      = $this->sourceFileFactory->create($composer);
        $exclusions = $this->getExclusions($composer);
        $symbols    = $this->extractor->extract($files, ...$exclusions);
        $violations = $this->finder->find($composer, $symbols);

        return new ViolationIterator(
            ...array_filter(
                iterator_to_array($violations),
                new ViolationFilter($composer)
            )
        );
    }

    /**
     * Get the exclusions from the Composer root package.
     *
     * @param Composer $composer
     *
     * @return string[]
     */
    private function getExclusions(Composer $composer): array
    {
        $extra = $composer->getPackage()->getExtra();

        return $extra['dependency-guard']['exclude'] ?? [];
    }
}
