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

class IsReadableCest
{
    /**
     * Tests Phalcon\Http\Message\Stream :: isReadable()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsReadable(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Stream - isReadable() - ' . $example['label']);

        $stream = $example['resource'];

        $expected = $example['readable'];
        $actual   = $stream->isReadable();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: isReadable() - with "x"
     *
     * @dataProvider getExamplesX
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageStreamIsReadableWithX(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Stream - isReadable() with "x" - ' . $example[0]);

        $fileName = $I->getNewFileName();
        $fileName = logsDir($fileName);

        $stream = new Stream($fileName, $example[0]);

        $I->assertSame(
            $example[1],
            $stream->isReadable()
        );
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
                'readable' => false,
            ],
            [
                'label'    => 'stream - ab',
                'resource' => new Stream($fileName, 'ab'),
                'readable' => false,
            ],
            [
                'label'    => 'stream - a+',
                'resource' => new Stream($fileName, 'a+'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - a+b',
                'resource' => new Stream($fileName, 'a+b'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - c',
                'resource' => new Stream($fileName, 'c'),
                'readable' => false,
            ],
            [
                'label'    => 'stream - cb',
                'resource' => new Stream($fileName, 'cb'),
                'readable' => false,
            ],
            [
                'label'    => 'stream - c+',
                'resource' => new Stream($fileName, 'c+'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - c+b',
                'resource' => new Stream($fileName, 'c+b'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - r',
                'resource' => new Stream($fileName, 'r'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - rb',
                'resource' => new Stream($fileName, 'rb'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - r+',
                'resource' => new Stream($fileName, 'r+'),
                'readable' => true,
            ],
            [
                'label'    => 'stream - r+b',
                'resource' => new Stream($fileName, 'r+b'),
                'readable' => true,
            ],
            [
                'label'    => 'input',
                'resource' => new Input(),
                'readable' => true,
            ],
            [
                'label'    => 'memory - a',
                'resource' => new Memory('a'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - ab',
                'resource' => new Memory('ab'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - a+',
                'resource' => new Memory('a+'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - a+b',
                'resource' => new Memory('a+b'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - c',
                'resource' => new Memory('c'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - cb',
                'resource' => new Memory('cb'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - c+',
                'resource' => new Memory('c+'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - c+b',
                'resource' => new Memory('c+b'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - r',
                'resource' => new Memory('r'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - rb',
                'resource' => new Memory('rb'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - r+',
                'resource' => new Memory('r+'),
                'readable' => true,
            ],
            [
                'label'    => 'memory - r+b',
                'resource' => new Memory('r+b'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - a',
                'resource' => new Temp('a'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - ab',
                'resource' => new Temp('ab'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - a+',
                'resource' => new Temp('a+'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - a+b',
                'resource' => new Temp('a+b'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - c',
                'resource' => new Temp('c'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - cb',
                'resource' => new Temp('cb'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - c+',
                'resource' => new Temp('c+'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - c+b',
                'resource' => new Temp('c+b'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - r',
                'resource' => new Temp('r'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - rb',
                'resource' => new Temp('rb'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - r+',
                'resource' => new Temp('r+'),
                'readable' => true,
            ],
            [
                'label'    => 'temp - r+b',
                'resource' => new Temp('r+b'),
                'readable' => true,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getExamplesX(): array
    {
        return [
            ['w', false],
            ['wb', false],
            ['w+', true],
            ['w+b', true],
            ['x', false],
            ['xb', false],
            ['x+', true],
            ['x+b', true],
        ];
    }
}
