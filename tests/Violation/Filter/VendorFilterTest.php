<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Mediact\DependencyGuard\Violation\Filter\VendorFilter;
use Mediact\DependencyGuard\Violation\ViolationInterface;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\VendorFilter
 */
class VendorFilterTest extends FilterTestCase
{
    /**
     * @dataProvider violationProvider
     *
     * @param string             $pattern
     * @param ViolationInterface $violation
     * @param bool               $expected
     *
     * @return void
     *
     * @covers ::__invoke
     * @covers ::__construct
     */
    public function testFilter(
        string $pattern,
        ViolationInterface $violation,
        bool $expected
    ): void {
        $subject = new VendorFilter($pattern);
        $this->assertInstanceOf(VendorFilter::class, $subject);
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
                true
            ],
            [
                'bar/',
                $this->createViolation(
                    $this->createPackage('baz/foo')
                ),
                true
            ],
            [
                'bar',
                $this->createViolation(
                    $this->createPackage('bar/baz')
                ),
                false
            ],
            [
                'bar/',
                $this->createViolation(
                    $this->createPackage('bar/baz')
                ),
                false
            ]
        ];
    }
}
