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

use Codeception\Example;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\Stream\Input;
use Phalcon\Http\Message\Stream\Memory;
use Phalcon\Http\Message\Stream\Temp;
use UnitTester;

use function dataDir;
use function logsDir;

class IsSeekableCest
{
    /**
     * Tests Phalcon\Http\Message\Stream :: isSeekable()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsSeekable(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Stream - isSeekable() - ' . $example['label']);

        $stream = $example['resource'];

        $expected = $example['seekable'];
        $actual   = $stream->isSeekable();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: isSeekable() - with "x"
     *
     * @dataProvider getExamplesX
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsSeekableWithX(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\Stream - isSeekable() with "x" - ' . $example[0]
        );

        $fileName = $I->getNewFileName();
        $fileName = logsDir($fileName);

        $stream = new Stream($fileName, $example[0]);

        $expected = $example[1];
        $actual   = $stream->isSeekable();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        $fileName = dataDir('assets/stream/mit-empty.txt');

        return [
            [
                'label'    => 'stream - a',
                'resource' => new Stream($fileName, 'a'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - ab',
                'resource' => new Stream($fileName, 'ab'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - a+',
                'resource' => new Stream($fileName, 'a+'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - a+b',
                'resource' => new Stream($fileName, 'a+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - c',
                'resource' => new Stream($fileName, 'c'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - cb',
                'resource' => new Stream($fileName, 'cb'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - c+',
                'resource' => new Stream($fileName, 'c+'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - c+b',
                'resource' => new Stream($fileName, 'c+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - r',
                'resource' => new Stream($fileName, 'r'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - rb',
                'resource' => new Stream($fileName, 'rb'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - r+',
                'resource' => new Stream($fileName, 'r+'),
                'seekable' => true,
            ],
            [
                'label'    => 'stream - r+b',
                'resource' => new Stream($fileName, 'r+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'input',
                'resource' => new Input(),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - a',
                'resource' => new Memory('a'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - ab',
                'resource' => new Memory('ab'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - a+',
                'resource' => new Memory('a+'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - a+b',
                'resource' => new Memory('a+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - c',
                'resource' => new Memory('c'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - cb',
                'resource' => new Memory('cb'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - c+',
                'resource' => new Memory('c+'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - c+b',
                'resource' => new Memory('c+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - r',
                'resource' => new Memory('r'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - rb',
                'resource' => new Memory('rb'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - r+',
                'resource' => new Memory('r+'),
                'seekable' => true,
            ],
            [
                'label'    => 'memory - r+b',
                'resource' => new Memory('r+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - a',
                'resource' => new Temp('a'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - ab',
                'resource' => new Temp('ab'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - a+',
                'resource' => new Temp('a+'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - a+b',
                'resource' => new Temp('a+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - c',
                'resource' => new Temp('c'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - cb',
                'resource' => new Temp('cb'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - c+',
                'resource' => new Temp('c+'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - c+b',
                'resource' => new Temp('c+b'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - r',
                'resource' => new Temp('r'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - rb',
                'resource' => new Temp('rb'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - r+',
                'resource' => new Temp('r+'),
                'seekable' => true,
            ],
            [
                'label'    => 'temp - r+b',
                'resource' => new Temp('r+b'),
                'seekable' => true,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getExamplesX(): array
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
}
