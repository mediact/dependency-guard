<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Composer\Package\PackageInterface;
use Mediact\DependencyGuard\Violation\ViolationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class FilterTestCase extends TestCase
{
    /**
     * @param string $name
     *
     * @return PackageInterface
     */
    protected function createPackage(string $name): PackageInterface
    {
        /** @var PackageInterface|MockObject $package */
        $package = $this->createMock(PackageInterface::class);

        $package
            ->expects(self::any())
            ->method('getName')
            ->willReturn($name);

        return $package;
    }

    /**
     * @param PackageInterface $package
     *
     * @return ViolationInterface
     */
    protected function createViolation(
        PackageInterface $package
    ): ViolationInterface {
        /** @var ViolationInterface|MockObject $violation */
        $violation = $this->createMock(ViolationInterface::class);

        $violation
            ->expects(self::once())
            ->method('getPackage')
            ->willReturn($package);

        return $violation;
    }
}
