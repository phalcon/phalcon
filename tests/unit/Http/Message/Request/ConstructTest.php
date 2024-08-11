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
use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Http\Message\Request;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageRequestConstruct(): void
    {
        $request = new Request();

        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - body exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageRequestConstructExceptionBody(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid stream passed as a parameter'
        );

        (new Request('GET', null, false));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - exception headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageRequestConstructExceptionHeaders(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Headers needs to be either an array or an instance "
            . "of Phalcon\\Http\\Message\\Headers"
        );

        (new Request(
            'GET',
            '',
            Http::STREAM_MEMORY,
            false
        ));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - exception uri
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageRequestConstructExceptionUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid uri passed as a parameter'
        );

        (new Request('GET', false));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - headers with host
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageRequestConstructHeadersWithHost(): void
    {
        $request = new Request(
            'GET',
            'https://dev.phalcon.ld:8080/action',
            Http::STREAM_MEMORY,
            [
                'Host'          => ['test.phalcon.ld'],
                'Accept'        => [Http::CONTENT_TYPE_HTML],
                'Cache-Control' => ['max-age=0'],
            ]
        );

        $expected = [
            'Host'          => ['dev.phalcon.ld:8080'],
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }
}
