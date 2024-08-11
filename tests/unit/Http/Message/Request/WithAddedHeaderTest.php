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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Request;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class WithAddedHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: withAddedHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithAddedHeader(): void
    {
        $data = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];

        $request = new Request('GET', null, Http::STREAM_MEMORY, $data);

        $newInstance = $request->withAddedHeader(
            'Cache-Control',
            [
                'max-age=0',
            ]
        );

        $this->assertNotSame($request, $newInstance);

        $expected = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];

        $this->assertSame(
            $expected,
            $request->getHeaders()
        );

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $this->assertSame(
            $expected,
            $newInstance->getHeaders()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withAddedHeader() - empty value
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithAddedHeaderEmptyValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid header value: must be a string or ' .
            'array of strings; cannot be an empty array'
        );

        $request = new Request();
        $request->withAddedHeader('Cache-Control', []);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withAddedHeader() - merge
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithAddedHeaderMerge(): void
    {
        $data = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];

        $request = new Request('GET', null, Http::STREAM, $data);

        $newInstance = $request->withAddedHeader(
            'Accept',
            [
                'text/json',
            ]
        );

        $this->assertNotSame($request, $newInstance);

        $expected = [
            'Accept' => [
                Http::CONTENT_TYPE_HTML,
            ],
        ];

        $this->assertSame(
            $expected,
            $request->getHeaders()
        );

        $expected = [
            'Accept' => [
                Http::CONTENT_TYPE_HTML,
                'text/json',
            ],
        ];

        $this->assertSame(
            $expected,
            $newInstance->getHeaders()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withAddedHeader() - string value
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestWithAddedHeaderStringValue(): void
    {
        $data = [
            'Accept' => [Http::CONTENT_TYPE_HTML],
        ];

        $request = new Request('GET', null, Http::STREAM_MEMORY, $data);

        $newInstance = $request->withAddedHeader(
            'Cache-Control',
            'max-age=0'
        );

        $this->assertNotSame($request, $newInstance);

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $this->assertSame(
            $expected,
            $newInstance->getHeaders()
        );
    }
}
