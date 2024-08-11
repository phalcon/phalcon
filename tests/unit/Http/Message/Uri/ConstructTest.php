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
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Uri :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriConstruct(): void
    {
        $uri   = new Uri();
        $class = UriInterface::class;
        $this->assertInstanceOf($class, $uri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - individually
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriConstructIndividually(): void
    {
        $source = "https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#newspaper";
        $uri    = new Uri($source);

        $uri
            ->withScheme("https")
            ->withUserInfo("phalcon", "secret")
            ->withHost("dev.phalcon.ld")
            ->withPort(8080)
            ->withPath("/action")
            ->withQuery("param=value")
            ->withFragment("newspaper")
        ;

        $expected = "https";
        $actual   = $uri->getScheme();
        $this->assertSame($expected, $actual);

        $expected = "phalcon:secret";
        $actual   = $uri->getUserInfo();
        $this->assertSame($expected, $actual);

        $expected = "phalcon:secret@dev.phalcon.ld:8080";
        $actual   = $uri->getAuthority();
        $this->assertSame($expected, $actual);

        $expected = "dev.phalcon.ld";
        $actual   = $uri->getHost();
        $this->assertSame($expected, $actual);

        $expected = 8080;
        $actual   = $uri->getPort();
        $this->assertSame($expected, $actual);

        $expected = "/action";
        $actual   = $uri->getPath();
        $this->assertSame($expected, $actual);

        $expected = "param=value";
        $actual   = $uri->getQuery();
        $this->assertSame($expected, $actual);

        $expected = "newspaper";
        $actual   = $uri->getFragment();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - malformed uri
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriConstructMalformedUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The URI cannot be parsed');

        (new Uri('https://'));
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - parse
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriConstructParse(): void
    {
        $source = "https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#newspaper";
        $uri    = new Uri($source);

        $expected = "https";
        $actual   = $uri->getScheme();
        $this->assertSame($expected, $actual);

        $expected = "phalcon:secret";
        $actual   = $uri->getUserInfo();
        $this->assertSame($expected, $actual);

        $expected = "phalcon:secret@dev.phalcon.ld:8080";
        $actual   = $uri->getAuthority();
        $this->assertSame($expected, $actual);

        $expected = "dev.phalcon.ld";
        $actual   = $uri->getHost();
        $this->assertSame($expected, $actual);

        $expected = 8080;
        $actual   = $uri->getPort();
        $this->assertSame($expected, $actual);

        $expected = "/action";
        $actual   = $uri->getPath();
        $this->assertSame($expected, $actual);

        $expected = "param=value";
        $actual   = $uri->getQuery();
        $this->assertSame($expected, $actual);

        $expected = "newspaper";
        $actual   = $uri->getFragment();
        $this->assertSame($expected, $actual);
    }
}
