<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Functional\Composer\Locker;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Composer\Locker\PackageRequirementsResolver;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class PackageRequirementsResolverTest extends TestCase
{
    /**
     * @dataProvider dependentsProvider
     *
     * @param Locker $locker
     * @param string $package
     * @param string ...$dependents
     *
     * @return void
     *
     * @coversNothing
     */
    public function testDependentsArePresent(
        Locker $locker,
        string $package,
        string ...$dependents
    ): void {
        $resolver = new PackageRequirementsResolver();
        $result   = $resolver->getDependents($package, $locker);
        $packages = array_map(
            function (PackageInterface $package) : string {
                return $package->getName();
            },
            $result
        );

        foreach ($dependents as $dependent) {
            $this->assertContains($dependent, $packages);
        }
    }

    /**
     * @return Locker
     */
    private function createLocker(): Locker
    {
        putenv(
            sprintf(
                'COMPOSER=%s',
                realpath(__DIR__ . '/composer.json') ?: ''
            )
        );

        return Factory::create(new NullIO())->getLocker();
    }

    /**
     * @return array
     */
    public function dependentsProvider(): array
    {
        $locker = $this->createLocker();

        return [
            [$locker, 'zendframework/zend-filter', 'magento/framework']
        ];
    }

    /**
     * @dataProvider invalidDependentsProvider
     *
     * @param Locker $locker
     * @param string $package
     * @param string ...$invalidDependents
     *
     * @return void
     *
     * @coversNothing
     */
    public function testDependentsAreNotPresent(
        Locker $locker,
        string $package,
        string ...$invalidDependents
    ): void {
        $resolver = new PackageRequirementsResolver();
        $result   = $resolver->getDependents($package, $locker);
        $packages = array_map(
            function (PackageInterface $package) : string {
                return $package->getName();
            },
            $result
        );

        foreach ($invalidDependents as $dependent) {
            $this->assertNotContains($dependent, $packages);
        }
    }

    /**
     * @return array
     */
    public function invalidDependentsProvider(): array
    {
        $locker = $this->createLocker();

        return [
            [$locker, 'zendframework/zend-filter', 'composer/composer']
        ];
    }
}
