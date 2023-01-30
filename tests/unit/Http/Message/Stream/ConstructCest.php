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
use Page\Http;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\Stream\Input;
use Phalcon\Http\Message\Stream\Memory;
use Phalcon\Http\Message\Stream\Temp;
use RuntimeException;
use stdClass;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Http\Message\Stream :: __construct()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-08
     */
    public function httpMessageStreamConstruct(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\Stream - __construct() ' . $example['label']
        );

        $request = $example['request'];

        $I->assertInstanceOf(StreamInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: __construct() - exception
     *
     * @dataProvider getExceptionExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-08
     */
    public function httpMessageStreamConstructException(
        UnitTester $I,
        Example $example
    ) {
        $I->wantToTest('Http\Message\Stream - __construct() ' . $example[0]);
        $I->expectThrowable(
            new RuntimeException(
                'The stream provided is not valid ' .
                '(string/resource) or could not be opened.'
            ),
            function () use ($example) {
                $request = new Stream($example[1]);
            }
        );
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'label'   => 'stream',
                'request' => new Stream(Http::STREAM_TEMP),
            ],
            [
                'label'   => 'input',
                'request' => new Input(),
            ],
            [
                'label'   => 'memory',
                'request' => new Memory(),
            ],
            [
                'label'   => 'temp',
                'request' => new Temp(),
            ],

        ];
    }

    /**
     * @return array[]
     */
    private function getExceptionExamples(): array
    {
        return [
            [
                'array',
                ['array'],
            ],
            [
                'boolean',
                true,
            ],
            [
                'float',
                123.45,
            ],
            [
                'integer',
                123,
            ],
            [
                'null',
                null,
            ],
            [
                'object',
                new stdClass(),
            ],
        ];
    }
}
