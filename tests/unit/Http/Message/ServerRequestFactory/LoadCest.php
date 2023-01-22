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

use Codeception\Example;
use Page\Http;
use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Factories\ServerRequestFactory;
use Phalcon\Http\Message\Interfaces\ServerRequestInterface;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Http\Message\ServerRequestFactoryFixture;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

/**
 * Class LoadCest
 *
 * @package Phalcon\Tests\Unit\Http\Message\ServerRequestFactory
 *
 * @property array $storeCookie
 * @property array $storeFiles
 * @property array $storeGet
 * @property array $storePost
 * @property array $storeServer
 */
class LoadCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoad(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load()');

        $factory = new ServerRequestFactory();
        $request = $factory->load();

        $I->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header cookie
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadHeaderCookie(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - header cookie');

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
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header host
     * prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadHeaderHost(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - header host');

        $server = [
            'HTTP_HOST' => 'dev.phalcon.ld:8080',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $I->assertSame('dev.phalcon.ld', $uri->getHost());
        $I->assertSame(8080, $uri->getPort());
        $I->assertSame('/', $uri->getPath());
        $I->assertSame('', $uri->getQuery());
        $I->assertSame('', $uri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - header host
     * array prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadHeaderHostArray(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - header host array');

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
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - IIS path
     * name/port prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadIisPath(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - IIS path');

        $server = [
            'IIS_WasUrlRewritten' => '1',
            'UNENCODED_URL'       => '/action/reaction',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $expected = '/action/reaction';
        $actual   = $uri->getPath();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - original
     * path info name/port prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadOriginalPathInfo(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - original path info');

        $server = [
            'ORIG_PATH_INFO' => '/action/reaction',
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $expected = '/action/reaction';
        $actual   = $uri->getPath();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server header
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadServerHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - server header');

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
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server
     * name/port prefixed
     *
     * @dataProvider getServerNameExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadServerNamePort(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\ServerRequestFactory - load() - server name/port '
            . $example['label']
        );

        $server = [
            'REQUEST_URI'  => $example['uri'],
            'QUERY_STRING' => $example['query'],
            'SERVER_NAME'  => $example['name'],
            'SERVER_PORT'  => $example['port'],
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load($server);
        $uri     = $request->getUri();

        $I->assertSame($example['getHost'], $uri->getHost());
        $I->assertSame($example['getPort'], $uri->getPort());
        $I->assertSame($example['getPath'], $uri->getPath());
        $I->assertSame($example['getQuery'], $uri->getQuery());
        $I->assertSame($example['getFragment'], $uri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - server
     * prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadServerPrefixed(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - server prefixed');

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
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - files
     * prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadFiles(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - files');

        $uploadObject = new UploadedFile(
            Http::STREAM_TEMP,
            0,
            0,
            'test2',
            Http::HEADERS_CONTENT_TYPE_PLAIN
        );

        $files = [
            [
                'tmp_name' => Http::STREAM_TEMP,
                'size'     => 0,
                'error'    => 0,
                'name'     => 'test1',
                'type'     => Http::HEADERS_CONTENT_TYPE_PLAIN,
            ],
            $uploadObject,
            [
                [
                    'tmp_name' => Http::STREAM_TEMP,
                    'size'     => 0,
                    'error'    => 0,
                    'name'     => 'test3',
                    'type'     => Http::HEADERS_CONTENT_TYPE_PLAIN,
                ],
            ],
        ];

        $factory = new ServerRequestFactory();
        $request = $factory->load(null, null, null, null, $files);

        $actual = $request->getUploadedFiles();

        /** @var UploadedFile $element */
        $element = $actual[0];
        $I->assertInstanceOf(UploadedFile::class, $element);
        $I->assertSame('test1', $element->getClientFilename());
        $I->assertSame(Http::HEADERS_CONTENT_TYPE_PLAIN, $element->getClientMediaType());

        /** @var UploadedFile $element */
        $element = $actual[1];
        $I->assertInstanceOf(UploadedFile::class, $element);
        $I->assertSame($uploadObject, $element);

        /** @var UploadedFile $element */
        $element = $actual[2][0];
        $I->assertInstanceOf(UploadedFile::class, $element);
        $I->assertSame('test3', $element->getClientFilename());
        $I->assertSame(Http::HEADERS_CONTENT_TYPE_PLAIN, $element->getClientMediaType());
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - files
     * exception prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadFilesException(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - files exception');

        $I->expectThrowable(
            new InvalidArgumentException(
                'The file array must contain tmp_name, size and error; ' .
                'one or more are missing'
            ),
            function () {
                $files = [
                    [
                        'tmp_name' => Http::STREAM_TEMP,
                        'size'     => 0,
                        'name'     => 'test1',
                        'type'     => Http::HEADERS_CONTENT_TYPE_PLAIN,
                    ],
                ];

                $factory = new ServerRequestFactory();
                $request = $factory->load(null, null, null, null, $files);
            }
        );
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - scheme https
     * prefixed
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadSchemeHttps(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - scheme https');

        $factory = new ServerRequestFactory();
        $server  = [
            'HTTPS' => 'on',
        ];

        $request = $factory->load($server);
        $uri     = $request->getUri();
        $I->assertSame('https', $uri->getScheme());

        $server = [
            'HTTPS' => 'off',
        ];

        $request  = $factory->load($server);
        $uri      = $request->getUri();
        $expected = 'http';
        $actual   = $uri->getScheme();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     *
     * @dataProvider getConstructorExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadConstructor(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\ServerRequestFactory - load() - constructor ' .
            $example['label']
        );

        $factory = new ServerRequestFactory();
        $request = $factory->load(
            $example[Http::HEADERS_SERVER],
            $example['get'],
            $example['post'],
            $example['cookies'],
            $example['files']
        );

        $I->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-05
     * @issue  15286
     */
    public function httpMessageServerRequestFactoryLoadConstructorFromSuperglobals(UnitTester $I)
    {
        $I->wantToTest(
            'Http\Message\ServerRequestFactory - load() - constructor from superglobals'
        );

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
        $I->assertSame($expected, $actual);

        $expected = $params;
        $actual   = $request->getServerParams();
        $I->assertSame($expected, $actual);

        $I->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - constructor
     * - empty superglobals
     *
     * @dataProvider getConstructorExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadConstructorEmptySuperglobals(UnitTester $I, Example $example)
    {
        $I->wantToTest(
            'Http\Message\ServerRequestFactory - load() - constructor - empty superglobals '
            . $example['label']
        );

        $factory = new ServerRequestFactory();
        $request = $factory->load(
            $example[Http::HEADERS_SERVER],
            $example['get'],
            $example['post'],
            $example['cookies'],
            $example['files']
        );
        $I->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * default
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadProtocolDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - protocol default');

        $factory = new ServerRequestFactory();

        $request = $factory->load();

        $expected = '1.1';
        $actual   = $request->getProtocolVersion();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * defined
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadProtocolDefined(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - protocol defined');

        $factory = new ServerRequestFactory();
        $server  = [
            'SERVER_PROTOCOL' => 'HTTP/2.0',
        ];

        $request = $factory->load($server);

        $expected = '2.0';
        $actual   = $request->getProtocolVersion();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * error
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadProtocolError(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - protocol error');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Incorrect protocol value HTTX/4.5'
            ),
            function () {
                $factory = new ServerRequestFactory();

                $server = [
                    'SERVER_PROTOCOL' => 'HTTX/4.5',
                ];

                $request = $factory->load($server);
            }
        );
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: load() - protocol
     * error unsupported
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageServerRequestFactoryLoadProtocolErrorUnsupported(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequestFactory - load() - protocol error unsupported');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Unsupported protocol HTTP/4.5'
            ),
            function () {
                $factory = new ServerRequestFactory();

                $server = [
                    'SERVER_PROTOCOL' => 'HTTP/4.5',
                ];

                $request = $factory->load($server);
            }
        );
    }

    /**
     * @return array
     */
    private function getConstructorExamples(): array
    {
        return [
            [
                'label'   => 'empty',
                Http::HEADERS_SERVER  => null,
                'get'     => null,
                'post'    => null,
                'cookies' => null,
                'files'   => null,
            ],
            [
                'label'   => Http::HEADERS_SERVER,
                Http::HEADERS_SERVER  => ['one' => 'two'],
                'get'     => null,
                'post'    => null,
                'cookies' => null,
                'files'   => null,
            ],
            [
                'label'   => 'get',
                Http::HEADERS_SERVER  => null,
                'get'     => ['one' => 'two'],
                'post'    => null,
                'cookies' => null,
                'files'   => null,
            ],
            [
                'label'   => 'post',
                Http::HEADERS_SERVER  => null,
                'get'     => null,
                'post'    => ['one' => 'two'],
                'cookies' => null,
                'files'   => null,
            ],
            [
                'label'   => 'cookie',
                Http::HEADERS_SERVER  => null,
                'get'     => null,
                'post'    => null,
                'cookies' => ['one' => 'two'],
                'files'   => null,
            ],
            [
                'label'   => 'files',
                Http::HEADERS_SERVER  => null,
                'get'     => null,
                'post'    => null,
                'cookies' => null,
                'files'   => ['one' => 'two'],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getServerNameExamples(): array
    {
        return [
            [
                'label'       => 'host',
                'uri'         => 'http://dev.phalcon.ld',
                'query'       => '',
                'name'        => 'dev.phalcon.ld',
                'port'        => null,
                'getHost'     => 'dev.phalcon.ld',
                'getPort'     => null,
                'getPath'     => '',
                'getQuery'    => '',
                'getFragment' => '',
            ],
            [
                'label'       => 'host port',
                'uri'         => 'http://dev.phalcon.ld',
                'query'       => '',
                'name'        => 'dev.phalcon.ld',
                'port'        => 8080,
                'getHost'     => 'dev.phalcon.ld',
                'getPort'     => 8080,
                'getPath'     => '',
                'getQuery'    => '',
                'getFragment' => '',
            ],
            [
                'label'       => 'host port path',
                'uri'         => 'http://dev.phalcon.ld/action/reaction',
                'query'       => '',
                'name'        => 'dev.phalcon.ld',
                'port'        => 8080,
                'getHost'     => 'dev.phalcon.ld',
                'getPort'     => 8080,
                'getPath'     => '/action/reaction',
                'getQuery'    => '',
                'getFragment' => '',
            ],
            [
                'label'       => 'host port path query',
                'uri'         => 'http://dev.phalcon.ld/action/reaction?one=two',
                'query'       => 'one=two',
                'name'        => 'dev.phalcon.ld',
                'port'        => 8080,
                'getHost'     => 'dev.phalcon.ld',
                'getPort'     => 8080,
                'getPath'     => '/action/reaction',
                'getQuery'    => 'one=two',
                'getFragment' => '',
            ],
            [
                'label'       => 'host port path query fragment',
                'uri'         => 'http://dev.phalcon.ld/action/reaction?one=two#fragment',
                'query'       => 'one=two',
                'name'        => 'dev.phalcon.ld',
                'port'        => 8080,
                'getHost'     => 'dev.phalcon.ld',
                'getPort'     => 8080,
                'getPath'     => '/action/reaction',
                'getQuery'    => 'one=two',
                'getFragment' => 'fragment',
            ],
        ];
    }
}
