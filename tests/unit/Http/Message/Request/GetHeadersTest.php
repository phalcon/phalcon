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

use Phalcon\Http\Message\Headers;
use Phalcon\Http\Message\Request;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetHeadersTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetHeaders(): void
    {
        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $data
        );

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders() - collection
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetHeadersCollection(): void
    {
        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $headers = new Headers($data);

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $headers
        );

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetHeadersEmpty(): void
    {
        $request = new Request();

        $expected = [];
        $actual   = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }
}
