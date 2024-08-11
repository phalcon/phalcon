<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http\Message\Stream;

use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\Stream\Input;
use Phalcon\Http\Message\Stream\Memory;
use Phalcon\Http\Message\Stream\Temp;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;
use function logsDir;

final class IsSeekableTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        $fileName = dataDir('assets/stream/mit-empty.txt');

        return [
            [
                new Stream($fileName, 'a'),
                true,
            ],
            [
                new Stream($fileName, 'ab'),
                true,
            ],
            [
                new Stream($fileName, 'a+'),
                true,
            ],
            [
                new Stream($fileName, 'a+b'),
                true,
            ],
            [
                new Stream($fileName, 'c'),
                true,
            ],
            [
                new Stream($fileName, 'cb'),
                true,
            ],
            [
                new Stream($fileName, 'c+'),
                true,
            ],
            [
                new Stream($fileName, 'c+b'),
                true,
            ],
            [
                new Stream($fileName, 'r'),
                true,
            ],
            [
                new Stream($fileName, 'rb'),
                true,
            ],
            [
                new Stream($fileName, 'r+'),
                true,
            ],
            [
                new Stream($fileName, 'r+b'),
                true,
            ],
            [
                new Input(),
                true,
            ],
            [
                new Memory('a'),
                true,
            ],
            [
                new Memory('ab'),
                true,
            ],
            [
                new Memory('a+'),
                true,
            ],
            [
                new Memory('a+b'),
                true,
            ],
            [
                new Memory('c'),
                true,
            ],
            [
                new Memory('cb'),
                true,
            ],
            [
                new Memory('c+'),
                true,
            ],
            [
                new Memory('c+b'),
                true,
            ],
            [
                new Memory('r'),
                true,
            ],
            [
                new Memory('rb'),
                true,
            ],
            [
                new Memory('r+'),
                true,
            ],
            [
                new Memory('r+b'),
                true,
            ],
            [
                new Temp('a'),
                true,
            ],
            [
                new Temp('ab'),
                true,
            ],
            [
                new Temp('a+'),
                true,
            ],
            [
                new Temp('a+b'),
                true,
            ],
            [
                new Temp('c'),
                true,
            ],
            [
                new Temp('cb'),
                true,
            ],
            [
                new Temp('c+'),
                true,
            ],
            [
                new Temp('c+b'),
                true,
            ],
            [
                new Temp('r'),
                true,
            ],
            [
                new Temp('rb'),
                true,
            ],
            [
                new Temp('r+'),
                true,
            ],
            [
                new Temp('r+b'),
                true,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function getExamplesX(): array
    {
        return [
            ['wb', true],
            ['w+', true],
            ['w+b', true],
            ['x', true],
            ['xb', true],
            ['x+', true],
            ['x+b', true],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: isSeekable()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function testHttpMessageStreamIsSeekable(
        mixed $stream,
        bool $expected
    ): void {
        $actual = $stream->isSeekable();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: isSeekable() - with "x"
     *
     * @dataProvider getExamplesX
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function testHttpMessageStreamIsSeekableWithX(
        string $mode,
        bool $expected
    ): void {
        $fileName = $this->getNewFileName();
        $fileName = logsDir($fileName);

        $stream = new Stream($fileName, $mode);

        $actual = $stream->isSeekable();
        $this->assertSame($expected, $actual);
    }
}
