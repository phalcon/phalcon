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
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - basic
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestConstruct(): void
    {
        $request = new Request('GET', 'https://example.com/path');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com/path', (string) $request->getUri());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - with Uri object
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestConstructWithUriObject(): void
    {
        $uri     = new Uri('https://example.com');
        $request = new Request('POST', $uri);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($uri, $request->getUri());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - null URI
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestConstructWithNullUri(): void
    {
        $request = new Request('GET', null);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('', (string) $request->getUri());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withMethod()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithMethod(): void
    {
        $request    = new Request('GET', 'https://example.com');
        $newRequest = $request->withMethod('POST');

        $this->assertNotSame($request, $newRequest);
        $this->assertSame('POST', $newRequest->getMethod());
        $this->assertSame('GET', $request->getMethod());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withUri()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithUri(): void
    {
        $request = new Request('GET', 'https://example.com');
        $newUri  = new Uri('https://other.com');

        $newRequest = $request->withUri($newUri);

        $this->assertNotSame($request, $newRequest);
        $this->assertSame('other.com', $newRequest->getUri()->getHost());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getRequestTarget()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestGetRequestTarget(): void
    {
        $request = new Request('GET', 'https://example.com/path?q=1');

        $this->assertSame('/path?q=1', $request->getRequestTarget());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withRequestTarget()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithRequestTarget(): void
    {
        $request    = new Request('GET', 'https://example.com');
        $newRequest = $request->withRequestTarget('/new?q=1');

        $this->assertSame('/new?q=1', $newRequest->getRequestTarget());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withHeader() / getHeader()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestHeaders(): void
    {
        $request    = new Request('GET', 'https://example.com');
        $newRequest = $request->withHeader('X-Custom', 'value1');

        $this->assertTrue($newRequest->hasHeader('X-Custom'));
        $this->assertSame(['value1'], $newRequest->getHeader('X-Custom'));
        $this->assertSame('value1', $newRequest->getHeaderLine('X-Custom'));
        $this->assertFalse($request->hasHeader('X-Custom'));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withAddedHeader()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithAddedHeader(): void
    {
        $request = (new Request('GET', 'https://example.com'))
            ->withHeader('X-Multi', 'first')
            ->withAddedHeader('X-Multi', 'second');

        $this->assertSame(['first', 'second'], $request->getHeader('X-Multi'));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withoutHeader()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithoutHeader(): void
    {
        $request = (new Request('GET', 'https://example.com'))
            ->withHeader('X-Remove', 'value')
            ->withoutHeader('X-Remove');

        $this->assertFalse($request->hasHeader('X-Remove'));
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getBody() / withBody()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestBody(): void
    {
        $request = new Request('POST', 'https://example.com');
        $body    = $request->getBody();

        $this->assertNotNull($body);
        $this->assertTrue($body->isWritable());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withProtocolVersion()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithProtocolVersion(): void
    {
        $request    = new Request('GET', 'https://example.com');
        $newRequest = $request->withProtocolVersion('2.0');

        $this->assertSame('2.0', $newRequest->getProtocolVersion());
        $this->assertSame('1.1', $request->getProtocolVersion());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: invalid method throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestInvalidMethodThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or unsupported method INVALIDMETHOD');
        new Request('INVALIDMETHOD', 'https://example.com');
    }

    /**
     * Tests Phalcon\Http\Message\Request :: php://input body
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestPhpInputBody(): void
    {
        $request = new Request('GET', 'https://example.com', 'php://input');

        $this->assertNotNull($request->getBody());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaders() returns all headers
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestGetHeaders(): void
    {
        $request = new Request(
            'GET',
            'https://example.com',
            'php://memory',
            ['X-Test' => 'value', 'Accept' => 'application/json']
        );

        $headers = $request->getHeaders();
        $this->assertArrayHasKey('X-Test', $headers);
        $this->assertArrayHasKey('Accept', $headers);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getRequestTarget() - empty path
     * returns "/"
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestGetRequestTargetEmptyPath(): void
    {
        $request = new Request('GET', 'https://example.com');

        $this->assertSame('/', $request->getRequestTarget());
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withRequestTarget() - null returns
     * same instance
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithRequestTargetNullReturnsSame(): void
    {
        $request    = new Request('GET', 'https://example.com');
        $newRequest = $request->withRequestTarget(null);

        $this->assertSame($request, $newRequest);
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withRequestTarget() - whitespace
     * throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestWithRequestTargetWhitespaceThrows(): void
    {
        $request = new Request('GET', 'https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target: cannot contain whitespace');
        $request->withRequestTarget('/path with spaces');
    }

    /**
     * Tests Phalcon\Http\Message\Request :: __construct() - invalid URI throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageRequestConstructInvalidUriThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uri passed as a parameter');
        new Request('GET', 12345);
    }
}
