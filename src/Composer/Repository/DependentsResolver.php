<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Composer\Repository;

use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;

class DependentsResolver implements DependentsResolverInterface
{
    /** @var CompositeRepository */
    private $repository;

    /**
     * Constructor.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = new CompositeRepository([$repository]);
    }

    /**
     * Resolves the dependents of the package.
     *
     * @param string $packageName
     *
     * @return array
     */
    public function resolve(string $packageName): array
    {
        return $this->getDependents(
            $packageName,
            false,
            []
        );
    }

    /**
     * Retrieves the dependents of a package in a recursive way.
     *
     * @param string $packageName
     * @param bool   $returnContext
     * @param array  $context
     *
     * @return array
     */
    private function getDependents(
        string $packageName,
        bool $returnContext,
        array $context = []
    ): array {
        if (!isset($context[$packageName])) {
            $context[$packageName] = $this->repository->getDependents(
                $packageName,
                null,
                false,
                false
            );

            foreach ($context[$packageName] as $key => $dependent) {
                $dependentContext = $this->getDependents($key, true, $context);
                $context          = array_merge(
                    $context,
                    $dependentContext
                );

                $context[$packageName] = array_merge(
                    $context[$packageName],
                    $dependentContext[$key]
                );
            }
        }

        if (!$returnContext) {
            return $context[$packageName];
        }

        return $context;
    }
}
