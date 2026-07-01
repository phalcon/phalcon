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

namespace Phalcon\Tests\Unit\Http\Message\Uri;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - full URI parsing
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriConstruct(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path?q=1#frag');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('q=1', $uri->getQuery());
        $this->assertSame('frag', $uri->getFragment());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - empty URI
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriConstructEmpty(): void
    {
        $uri = new Uri('');

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getAuthority());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: standard port returns null
     *
     * Per RFC 3986, standard ports (80 for http, 443 for https) should
     * return null from getPort().
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriStandardPortReturnsNull(): void
    {
        $uri = new Uri('https://example.com:443');

        $this->assertNull($uri->getPort());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __toString()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriToString(): void
    {
        $uriString = 'https://example.com/path?q=1#frag';
        $uri       = new Uri($uriString);

        $this->assertSame($uriString, (string) $uri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __toString() - path with double slash, no
     * authority - must reduce to single slash per RFC 3986
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriToStringDoubleSlashNoAuthority(): void
    {
        $uri    = new Uri('');
        $newUri = $uri->withPath('//path/to/resource');

        $this->assertSame('/path/to/resource', (string) $newUri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __toString() - rootless path with
     * authority gets prefixed with "/"
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriToStringRootlessPathWithAuthority(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withPath('relative/path');

        $this->assertStringContainsString('/relative/path', (string) $newUri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withFragment()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithFragment(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withFragment('section1');

        $this->assertSame('section1', $newUri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withFragment() - hash prefix gets
     * encoded
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithFragmentHashPrefix(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withFragment('#section');

        $this->assertSame('%23section', $newUri->getFragment());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withHost()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithHost(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withHost('other.com');

        $this->assertSame('other.com', $newUri->getHost());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPath()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithPath(): void
    {
        $uri    = new Uri('https://example.com/old');
        $newUri = $uri->withPath('/new/path');

        $this->assertSame('/new/path', $newUri->getPath());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPath() - invalid path throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithPathInvalidThrows(): void
    {
        $uri = new Uri('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path cannot contain a query string or fragment');
        $uri->withPath('/path?query');
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithPort(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withPort(9090);

        $this->assertSame(9090, $newUri->getPort());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort() - null removes port
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithPortNull(): void
    {
        $uri    = new Uri('https://example.com:8080');
        $newUri = $uri->withPort(null);

        $this->assertNull($newUri->getPort());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort() - out of range throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithPortOutOfRangeThrows(): void
    {
        $uri = new Uri('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid port specified. (Valid range 1-65535)');
        $uri->withPort(70000);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithQuery(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withQuery('key=value');

        $this->assertSame('key=value', $newUri->getQuery());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery() - fragment throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithQueryFragmentThrows(): void
    {
        $uri = new Uri('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query cannot contain a URI fragment');
        $uri->withQuery('key=value#fragment');
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery() - no equals sign
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithQueryNoEquals(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withQuery('key1&key2');

        $this->assertSame('key1&key2', $newUri->getQuery());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: cloneInstance same value returns same
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithSameValueReturnsSame(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withScheme('https');

        $this->assertSame($uri, $newUri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithScheme(): void
    {
        $uri     = new Uri('https://example.com');
        $newUri  = $uri->withScheme('http');

        $this->assertNotSame($uri, $newUri);
        $this->assertSame('http', $newUri->getScheme());
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme() - unsupported throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithSchemeUnsupportedThrows(): void
    {
        $uri = new Uri('https://example.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported scheme [ftp]');
        $uri->withScheme('ftp');
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withUserInfo()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageUriWithUserInfo(): void
    {
        $uri    = new Uri('https://example.com');
        $newUri = $uri->withUserInfo('user', 'secret');

        $this->assertSame('user:secret', $newUri->getUserInfo());
    }
}
