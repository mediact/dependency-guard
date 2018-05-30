<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Violation\Filter;

use Mediact\DependencyGuard\Violation\ViolationInterface;
use Mediact\DependencyGuard\Violation\Filter\PatternPackageFilter;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Violation\Filter\PatternPackageFilter
 */
class PatternPackageFilterTest extends FilterTestCase
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
        $subject = new PatternPackageFilter($pattern);
        $this->assertInstanceOf(PatternPackageFilter::class, $subject);
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
            ],
            [
                'ba[rz]',
                $this->createViolation(
                    $this->createPackage('baz')
                ),
                false
            ],
            [
                'ba?',
                $this->createViolation(
                    $this->createPackage('baz')
                ),
                false
            ],
            [
                '*/ba[rz]',
                $this->createViolation(
                    $this->createPackage('foo/baz')
                ),
                false
            ]
        ];
    }
}
