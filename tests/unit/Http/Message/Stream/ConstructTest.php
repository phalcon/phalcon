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

use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\Stream\Input;
use Phalcon\Http\Message\Stream\Memory;
use Phalcon\Http\Message\Stream\Temp;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;
use RuntimeException;
use stdClass;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                new Stream(Http::STREAM_TEMP),
            ],
            [
                new Input(),
            ],
            [
                new Memory(),
            ],
            [
                new Temp(),
            ],

        ];
    }

    /**
     * @return array[]
     */
    public static function getExceptionExamples(): array
    {
        return [
            [
                ['array'],
            ],
            [
                true,
            ],
            [
                123.45,
            ],
            [
                123,
            ],
            [
                null,
            ],
            [
                new stdClass(),
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: __construct()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-08
     */
    public function testHttpMessageStreamConstruct(
        mixed $request
    ): void {
        $this->assertInstanceOf(StreamInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\Stream :: __construct() - exception
     *
     * @dataProvider getExceptionExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-08
     */
    public function testHttpMessageStreamConstructException(
        mixed $stream
    ) {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The stream provided is not valid ' .
            '(string/resource) or could not be opened.'
        );

        (new Stream($stream));
    }
}
