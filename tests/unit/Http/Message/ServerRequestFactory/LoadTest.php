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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequestFactory;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Factories\ServerRequestFactory;
use Phalcon\Http\Message\Interfaces\ServerRequestInterface;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Http\Message\ServerRequestFactoryFixture;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\Unit\Http\Helper\AbstractHttpBase;

/**
 * Class LoadTest extends AbstractUnitTestCase
 *
 * @package Phalcon\Tests\Unit\Http\Message\ServerRequestFactory
 *
 * @property array $storeCookie
 * @property array $storeFiles
 * @property array $storeGet
 * @property array $storePost
 * @property array $storeServer
 */
final class LoadTest extends AbstractHttpBase
{
    /**
     * @return array
     */
    public static function getConstructorExamples(): array
    {
        return [
            [
                null,
                null,
                null,
                null,
                null,
            ],
            [
                ['one' => 'two'],
                null,
                null,
                null,
                null,
            ],
            [
                null,
                ['one' => 'two'],
                null,
                null,
                null,
            ],
            [
                null,
                null,
                ['one' => 'two'],
                null,
                null,
            ],
            [
                null,
                null,
                null,
                ['one' => 'two'],
                null,
            ],
            [
                null,
                null,
                null,
                null,
                ['one' => 'two'],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getServerNameExamples(): array
    {
        return [
            [
                'https://dev.phalcon.ld',
                '',
                'dev.phalcon.ld',
                null,
                'dev.phalcon.ld',
                null,
                '',
                '',
                '',
            ],
            [
                'https://dev.phalcon.ld',
                '',
                'dev.phalcon.ld',
                8080,
                'dev.phalcon.ld',
                8080,
                '',
                '',
                '',
            ],
            [
                'https://dev.phalcon.ld/action/reaction',
                '',
                'dev.phalcon.ld',
                8080,
                'dev.phalcon.ld',
                8080,
                '/action/reaction',
                '',
                '',
            ],
            [
                'https://dev.phalcon.ld/action/reaction?one=two',
                'one=two',
                'dev.phalcon.ld',
                8080,
                'dev.phalcon.ld',
                8080,
                '/action/reaction',
                'one=two',
                '',
            ],
            [
                'https://dev.phalcon.ld/action/reaction?one=two#fragment',
                'one=two',
                'dev.phalcon.ld',
                8080,
                'dev.phalcon.ld',
                8080,
                '/action/reaction',
                'one=two',
                'fragment',
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoad(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->load();

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     *
     * @dataProvider getConstructorExamples
     *
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadConstructor(
        ?array $server,
        ?array $get,
        ?array $post,
        ?array $cookies,
        ?array $files
    ): void {
        $factory = new ServerRequestFactory();
        $request = $factory->load(
            $server,
            $get,
            $post,
            $cookies,
            $files
        );

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     * - empty superglobals
     *
     * @dataProvider getConstructorExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadConstructorEmptySuperglobals(
        ?array $server,
        ?array $get,
        ?array $post,
        ?array $cookies,
        ?array $files
    ): void {
        $factory = new ServerRequestFactory();
        $request = $factory->load(
            $server,
            $get,
            $post,
            $cookies,
            $files
        );
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-05
     * @issue  15286
     */
    public function testHttpMessageServerRequestFactoryLoadConstructorFromSuperglobals(): void
    {
        // Backup
        $params = [
            'REQUEST_TIME_FLOAT' => $this->store['SERVER']['REQUEST_TIME_FLOAT'],
            'REQUEST_METHOD'     => 'PUT',
            'one'                => 'two',
        ];

        $_SERVER = $params;

        $factory = new ServerRequestFactory();
        $request = $factory->load();

        $expected = 'PUT';
        $actual   = $request->getMethod();
        $this->assertSame($expected, $actual);

        $expected = $params;
        $actual   = $request->getServerParams();
        $this->assertSame($expected, $actual);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - files
     * prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadFiles(): void
    {
        $uploadObject = new UploadedFile(
            Http::STREAM_TEMP,
            0,
            0,
            'test2',
            Http::CONTENT_TYPE_PLAIN
        );

        $files = [
            [
                'tmp_name' => Http::STREAM_TEMP,
                'size'     => 0,
                'error'    => 0,
                'name'     => 'test1',
                'type'     => Http::CONTENT_TYPE_PLAIN,
            ],
            $uploadObject,
            [
                [
                    'tmp_name' => Http::STREAM_TEMP,
                    'size'     => 0,
                    'error'    => 0,
                    'name'     => 'test3',
                    'type'     => Http::CONTENT_TYPE_PLAIN,
                ],
            ],
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load(null, null, null, null, $files);

        $actual = $request->getUploadedFiles();

        /** @var UploadedFile $element */
        $element = $actual[0];
        $this->assertInstanceOf(UploadedFile::class, $element);
        $this->assertSame('test1', $element->getClientFilename());
        $this->assertSame(
            Http::CONTENT_TYPE_PLAIN,
            $element->getClientMediaType()
        );

        /** @var UploadedFile $element */
        $element = $actual[1];
        $this->assertInstanceOf(UploadedFile::class, $element);
        $this->assertSame($uploadObject, $element);

        /** @var UploadedFile $element */
        $element = $actual[2][0];
        $this->assertInstanceOf(UploadedFile::class, $element);
        $this->assertSame('test3', $element->getClientFilename());
        $this->assertSame(
            Http::CONTENT_TYPE_PLAIN,
            $element->getClientMediaType()
        );
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - files
     * exception prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadFilesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The file array must contain tmp_name, size and error; ' .
            'one or more are missing'
        );

        $files = [
            [
                'tmp_name' => Http::STREAM_TEMP,
                'size'     => 0,
                'name'     => 'test1',
                'type'     => Http::CONTENT_TYPE_PLAIN,
            ],
        ];

        $factory = new ServerRequestFactory();
        $factory->load(null, null, null, null, $files);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header cookie
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadHeaderCookie(): void
    {
        $server = [
            'HTTP_COOKIE' => 'TESTSESS=face28e8-daae-10e0-a774-00000abbdf6c:3447789008; ' .
                'expires=Sun, 08-Nov-2020 00:00:00 UTC; ' .
                'Max-Age=63071999; ' .
                'path=/; ' .
                'domain=.phalcon.ld; ' .
                'secure; httponly',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);

        $expected = [
            'TESTSESS' => 'face28e8-daae-10e0-a774-00000abbdf6c:3447789008',
            'expires'  => 'Sun, 08-Nov-2020 00:00:00 UTC',
            'Max-Age'  => '63071999',
            'path'     => '/',
            'domain'   => '.phalcon.ld',
            'secure'   => '',
            'httponly' => '',
        ];

        $actual = $request->getCookieParams();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header host
     * prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadHeaderHost(): void
    {
        $server = [
            'HTTP_HOST' => 'dev.phalcon.ld:8080',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $this->assertSame('dev.phalcon.ld', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header host
     * array prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadHeaderHostArray(): void
    {
        $server = [
            'HTTP_HOST' => [
                'dev.phalcon.ld',
                'test.phalcon.ld',
            ],
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $expected = 'dev.phalcon.ld,test.phalcon.ld';
        $actual   = $uri->getHost();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - IIS path
     * name/port prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadIisPath(): void
    {
        $server = [
            'IIS_WasUrlRewritten' => '1',
            'UNENCODED_URL'       => '/action/reaction',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $expected = '/action/reaction';
        $actual   = $uri->getPath();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - original
     * path info name/port prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadOriginalPathInfo(): void
    {
        $server = [
            'ORIG_PATH_INFO' => '/action/reaction',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $expected = '/action/reaction';
        $actual   = $uri->getPath();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * default
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadProtocolDefault(): void
    {
        $factory = new ServerRequestFactory();

        $request = $factory->load();

        $expected = '1.1';
        $actual   = $request->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * defined
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadProtocolDefined(): void
    {
        $factory = new ServerRequestFactory();
        $server  = [
            'SERVER_PROTOCOL' => 'HTTP/2.0',
        ];

        $request = $factory->load($server);

        $expected = '2.0';
        $actual   = $request->getProtocolVersion();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * error
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadProtocolError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect protocol value HTTX/4.5');

        $factory = new ServerRequestFactory();

        $server = [
            'SERVER_PROTOCOL' => 'HTTX/4.5',
        ];

        $factory->load($server);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * error unsupported
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadProtocolErrorUnsupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported protocol HTTP/4.5');

        $factory = new ServerRequestFactory();

        $server = [
            'SERVER_PROTOCOL' => 'HTTP/4.5',
        ];

        $factory->load($server);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - scheme https
     * prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadSchemeHttps(): void
    {
        $factory = new ServerRequestFactory();
        $server  = [
            'HTTPS' => 'on',
        ];

        $request = $factory->load($server);
        $uri     = $request->getUri();
        $this->assertSame('https', $uri->getScheme());

        $server = [
            'HTTPS' => 'off',
        ];

        $request  = $factory->load($server);
        $uri      = $request->getUri();
        $expected = 'http';
        $actual   = $uri->getScheme();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server header
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadServerHeader(): void
    {
        $server = [
            'HTTP_HOST' => 'test.phalcon.ld',
        ];

        $factory = new ServerRequestFactoryFixture();
        $request = $factory->load($server);

        /**
         * "Host" instead of "host" because there is no URI
         */
        $expected = [
            'Host'          => ['test.phalcon.ld'],
            'authorization' => ['Bearer'],
        ];

        $actual = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server
     * name/port prefixed
     *
     * @dataProvider getServerNameExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadServerNamePort(
        string $uri,
        string $query,
        string $name,
        ?int $port,
        string $getHost,
        ?int $getPort,
        string $getPath,
        string $getQuery,
        string $getFragment,
    ) {
        $server = [
            'REQUEST_URI'  => $uri,
            'QUERY_STRING' => $query,
            'SERVER_NAME'  => $name,
            'SERVER_PORT'  => $port,
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $this->assertSame($getHost, $uri->getHost());
        $this->assertSame($getPort, $uri->getPort());
        $this->assertSame($getPath, $uri->getPath());
        $this->assertSame($getQuery, $uri->getQuery());
        $this->assertSame($getFragment, $uri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server
     * prefixed
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryLoadServerPrefixed(): void
    {
        $server = [
            'SIMPLE'               => 'Some Cookie',
            'NO_OVERRIDE'          => 'auth-token',
            'REDIRECT_NO_OVERRIDE' => 'token-auth',
            'REDIRECT_OVERRIDE'    => 'override',
            'HTTP_AUTH'            => 'letmein',
            'CONTENT_LENGTH'       => 'UNSPECIFIED',
        ];

        $expected = [
            'auth'           => ['letmein'],
            'content-length' => ['UNSPECIFIED'],
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);

        $actual = $request->getHeaders();
        $this->assertSame($expected, $actual);
    }
}
