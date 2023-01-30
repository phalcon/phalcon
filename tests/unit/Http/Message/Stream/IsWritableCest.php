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

class IsWritableCest
{
    /**
     * Tests Phalcon\Http\Message\Stream :: isWritable()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsWritable(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\Stream - isWritable() - ' . $example['label']
        );

        $stream = $example['resource'];

        $expected = $example['writable'];
        $actual   = $stream->isWritable();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: isWritable() - with "x"
     *
     * @dataProvider getExamplesX
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsWritableWithX(
        UnitTester $I,
        Example $example
    ) {
        $I->wantToTest(
            'Http\Message\Stream - isWritable() with "x" - ' . $example[0]
        );

        $fileName = $I->getNewFileName();
        $fileName = logsDir($fileName);

        $stream = new Stream($fileName, $example[0]);

        $expected = $example[1];
        $actual   = $stream->isWritable();
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
                'writable' => true,
            ],
            [
                'label'    => 'stream - ab',
                'resource' => new Stream($fileName, 'ab'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - a+',
                'resource' => new Stream($fileName, 'a+'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - a+b',
                'resource' => new Stream($fileName, 'a+b'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - c',
                'resource' => new Stream($fileName, 'c'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - cb',
                'resource' => new Stream($fileName, 'cb'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - c+',
                'resource' => new Stream($fileName, 'c+'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - c+b',
                'resource' => new Stream($fileName, 'c+b'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - r',
                'resource' => new Stream($fileName, 'r'),
                'writable' => false,
            ],
            [
                'label'    => 'stream - rb',
                'resource' => new Stream($fileName, 'rb'),
                'writable' => false,
            ],
            [
                'label'    => 'stream - r+',
                'resource' => new Stream($fileName, 'r+'),
                'writable' => true,
            ],
            [
                'label'    => 'stream - r+b',
                'resource' => new Stream($fileName, 'r+b'),
                'writable' => true,
            ],
            [
                'label'    => 'input',
                'resource' => new Input(),
                'writable' => false,
            ],
            [
                'label'    => 'memory - a',
                'resource' => new Memory('a'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - ab',
                'resource' => new Memory('ab'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - a+',
                'resource' => new Memory('a+'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - a+b',
                'resource' => new Memory('a+b'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - c',
                'resource' => new Memory('c'),
                'writable' => false,
            ],
            [
                'label'    => 'memory - cb',
                'resource' => new Memory('cb'),
                'writable' => false,
            ],
            [
                'label'    => 'memory - c+',
                'resource' => new Memory('c+'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - c+b',
                'resource' => new Memory('c+b'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - r',
                'resource' => new Memory('r'),
                'writable' => false,
            ],
            [
                'label'    => 'memory - rb',
                'resource' => new Memory('rb'),
                'writable' => false,
            ],
            [
                'label'    => 'memory - r+',
                'resource' => new Memory('r+'),
                'writable' => true,
            ],
            [
                'label'    => 'memory - r+b',
                'resource' => new Memory('r+b'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - a',
                'resource' => new Temp('a'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - ab',
                'resource' => new Temp('ab'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - a+',
                'resource' => new Temp('a+'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - a+b',
                'resource' => new Temp('a+b'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - c',
                'resource' => new Temp('c'),
                'writable' => false,
            ],
            [
                'label'    => 'temp - cb',
                'resource' => new Temp('cb'),
                'writable' => false,
            ],
            [
                'label'    => 'temp - c+',
                'resource' => new Temp('c+'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - c+b',
                'resource' => new Temp('c+b'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - r',
                'resource' => new Temp('r'),
                'writable' => false,
            ],
            [
                'label'    => 'temp - rb',
                'resource' => new Temp('rb'),
                'writable' => false,
            ],
            [
                'label'    => 'temp - r+',
                'resource' => new Temp('r+'),
                'writable' => true,
            ],
            [
                'label'    => 'temp - r+b',
                'resource' => new Temp('r+b'),
                'writable' => true,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getExamplesX(): array
    {
        return [
            ['w', true],
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
