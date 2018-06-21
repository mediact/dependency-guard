<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\Tests\Iterator;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Mediact\DependencyGuard\Iterator\FileIterator;
use SplFileInfo;

/**
 * @coversDefaultClass \Mediact\DependencyGuard\Iterator\FileIterator
 */
class FileIteratorTest extends TestCase
{
    /**
     * @dataProvider fileProvider
     *
     * @param SplFileInfo ...$files
     *
     * @return void
     *
     * @covers ::current
     */
    public function testCurrent(SplFileInfo ...$files): void
    {
        $subject = new FileIterator(
            new ArrayIterator($files)
        );

        $numFiles = 0;

        foreach ($subject as $file) {
            $this->assertInstanceOf(SplFileInfo::class, $file);
            $numFiles++;
        }

        $this->assertEquals(count($files), $numFiles);
    }

    /**
     * @return SplFileInfo[][]
     */
    public function fileProvider(): array
    {
        return [
            [],
            [$this->createMock(SplFileInfo::class)],
            [
                $this->createMock(SplFileInfo::class),
                $this->createMock(SplFileInfo::class),
                $this->createMock(SplFileInfo::class)
            ]
        ];
    }
}
