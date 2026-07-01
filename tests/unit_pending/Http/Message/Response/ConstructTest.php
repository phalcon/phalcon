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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Response;
use Phalcon\Http\Message\Stream;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: __construct() - defaults
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseConstruct(): void
    {
        $response = new Response();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.1', $response->getProtocolVersion());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: __construct() - custom code
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseConstructCustomCode(): void
    {
        $response = new Response('php://memory', 404);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: __construct() - invalid body throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseConstructInvalidBodyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream passed as a parameter');
        new Response(12345);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withHeader() / getHeaders()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseHeaders(): void
    {
        $response    = new Response();
        $newResponse = $response->withHeader('Content-Type', 'application/json');

        $this->assertTrue($newResponse->hasHeader('Content-Type'));
        $this->assertSame(['application/json'], $newResponse->getHeader('Content-Type'));
        $this->assertFalse($response->hasHeader('Content-Type'));
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withBody()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithBody(): void
    {
        $response = new Response('php://memory', 200, ['X-Test' => 'val']);
        $body     = $response->getBody();

        $this->assertNotNull($body);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withBody() - replaces body
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithBodyReplaces(): void
    {
        $response = new Response();
        $stream   = new Stream('php://memory', 'r+b');
        $stream->write('hello');

        $newResponse = $response->withBody($stream);
        $this->assertSame('hello', (string) $newResponse->getBody());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withHeader() - invalid name throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithHeaderInvalidNameThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header name');
        $response->withHeader('Invalid Header!@#', 'value');
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withHeader() - invalid value throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithHeaderInvalidValueThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value');
        $response->withHeader('Content-Type', []);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withHeader() - non-string value
     * throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithHeaderNonStringValueThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value');
        $response->withHeader('Content-Type', [new \stdClass()]);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withHeader() - value with
     * invalid characters throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithHeaderValueInvalidCharsThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value');
        $response->withHeader('Content-Type', "value\x00invalid");
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withProtocolVersion()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithProtocolVersion(): void
    {
        $response    = new Response();
        $newResponse = $response->withProtocolVersion('2.0');

        $this->assertSame('2.0', $newResponse->getProtocolVersion());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withProtocolVersion() - empty
     * version throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithProtocolVersionEmptyThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid protocol value');
        $response->withProtocolVersion('');
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withProtocolVersion() - invalid
     * version throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithProtocolVersionInvalidThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported protocol');
        $response->withProtocolVersion('4.0');
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withStatus()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithStatus(): void
    {
        $response    = new Response();
        $newResponse = $response->withStatus(201, 'Created');

        $this->assertNotSame($response, $newResponse);
        $this->assertSame(201, $newResponse->getStatusCode());
        $this->assertSame('Created', $newResponse->getReasonPhrase());
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withStatus() - custom phrase
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithStatusCustomPhrase(): void
    {
        $response    = new Response();
        $newResponse = $response->withStatus(200, 'Custom OK');

        $this->assertSame('Custom OK', $newResponse->getReasonPhrase());
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withStatus() - invalid code throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageResponseWithStatusInvalidThrows(): void
    {
        $response = new Response();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid status code '99'");
        $response->withStatus(99);
    }
}
