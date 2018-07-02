<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Mediact\DependencyGuard\Violation\Filter\ExactPackageFilter;
use Mediact\DependencyGuard\Violation\ViolationInterface;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\ExactPackageFilter
 */
class ExactPackageFilterTest extends FilterTestCase
{
    /**
     * @dataProvider violationProvider
     *
     * @param string             $name
     * @param ViolationInterface $violation
     * @param bool               $expected
     *
     * @return void
     *
     * @covers ::__invoke
     * @covers ::__construct
     */
    public function testFilter(
        string $name,
        ViolationInterface $violation,
        bool $expected
    ): void {
        $subject = new ExactPackageFilter($name);
        $this->assertInstanceOf(ExactPackageFilter::class, $subject);
        $this->assertEquals($expected, $subject($violation));
    }

    /**
     * @return string[][]|ViolationInterface[][]|bool[][]
     */
    public function violationProvider(): array
    {
        return [
            [
                'foo',
                $this->createViolation(
                    $this->createPackage('foo')
                ),
                false
            ],
            [
                'bar',
                $this->createViolation(
                    $this->createPackage('baz')
                ),
                true
            ]
        ];
    }
}
