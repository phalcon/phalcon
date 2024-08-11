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

namespace Phalcon\Tests\Unit\Http\Message\Response;

use Phalcon\Http\Message\Response;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetHeadersTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: getHeaders()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeaders(): void
    {
        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $response = new Response(Http::STREAM_MEMORY, 200, $data);

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $response->getHeaders();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getHeaders() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeadersEmpty(): void
    {
        $response = new Response();

        $expected = [];
        $actual   = $response->getHeaders();
        $this->assertSame($expected, $actual);
    }
}
