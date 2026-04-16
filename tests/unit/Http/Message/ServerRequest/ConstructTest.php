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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\Stream;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: __construct() - basic
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestConstruct(): void
    {
        $request = new ServerRequest('GET', 'https://example.com');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('example.com', $request->getUri()->getHost());
        $this->assertSame([], $request->getServerParams());
        $this->assertSame([], $request->getCookieParams());
        $this->assertSame([], $request->getQueryParams());
        $this->assertSame([], $request->getUploadedFiles());
        $this->assertNull($request->getParsedBody());
        $this->assertSame([], $request->getAttributes());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withCookieParams()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithCookieParams(): void
    {
        $request    = new ServerRequest('GET', 'https://example.com');
        $newRequest = $request->withCookieParams(['session' => 'abc123']);

        $this->assertSame(['session' => 'abc123'], $newRequest->getCookieParams());
        $this->assertSame([], $request->getCookieParams());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withQueryParams()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithQueryParams(): void
    {
        $request    = new ServerRequest('GET', 'https://example.com');
        $newRequest = $request->withQueryParams(['page' => '2']);

        $this->assertSame(['page' => '2'], $newRequest->getQueryParams());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withParsedBody()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithParsedBody(): void
    {
        $request    = new ServerRequest('POST', 'https://example.com');
        $newRequest = $request->withParsedBody(['name' => 'test']);

        $this->assertSame(['name' => 'test'], $newRequest->getParsedBody());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withAttribute() /
     * getAttribute() / withoutAttribute()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestAttributes(): void
    {
        $request = new ServerRequest('GET', 'https://example.com');

        $withAttr = $request->withAttribute('user', 'john');
        $this->assertSame('john', $withAttr->getAttribute('user'));
        $this->assertSame('default', $withAttr->getAttribute('missing', 'default'));
        $this->assertSame(['user' => 'john'], $withAttr->getAttributes());

        $withoutAttr = $withAttr->withoutAttribute('user');
        $this->assertNull($withoutAttr->getAttribute('user'));
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithUploadedFiles(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, 0, 0, 'test.txt', 'text/plain');

        $request    = new ServerRequest('POST', 'https://example.com');
        $newRequest = $request->withUploadedFiles(['file' => $uploaded]);

        $files = $newRequest->getUploadedFiles();
        $this->assertArrayHasKey('file', $files);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles() -
     * invalid files throw
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithUploadedFilesInvalidThrows(): void
    {
        $request = new ServerRequest('POST', 'https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $request->withUploadedFiles(['file' => 'not-an-uploaded-file']);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withServerParams()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithServerParams(): void
    {
        $params  = ['REQUEST_METHOD' => 'GET', 'SERVER_NAME' => 'localhost'];
        $request = new ServerRequest('GET', 'https://example.com', $params);

        $this->assertSame($params, $request->getServerParams());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withParsedBody() - invalid
     * value throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithParsedBodyInvalidThrows(): void
    {
        $request = new ServerRequest('POST', 'https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The method expects null, an array or an object');
        $request->withParsedBody('invalid-string');
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles() - nested
     * array of uploaded files
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageServerRequestWithUploadedFilesNested(): void
    {
        $stream   = new Stream('php://memory', 'r+b');
        $uploaded = new UploadedFile($stream, 0, 0, 'test.txt', 'text/plain');

        $request    = new ServerRequest('POST', 'https://example.com');
        $newRequest = $request->withUploadedFiles(['files' => [$uploaded]]);

        $files = $newRequest->getUploadedFiles();
        $this->assertArrayHasKey('files', $files);
        $this->assertIsArray($files['files']);
    }
}
