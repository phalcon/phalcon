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
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriConstruct(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - __construct()');

        $uri   = new Uri();
        $class = UriInterface::class;
        $I->assertInstanceOf($class, $uri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - parse
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriConstructParse(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - __construct() - parse');

        $source = "https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#newspaper";
        $uri    = new Uri($source);

        $expected = "https";
        $actual   = $uri->getScheme();
        $I->assertSame($expected, $actual);

        $expected = "phalcon:secret";
        $actual   = $uri->getUserInfo();
        $I->assertSame($expected, $actual);

        $expected = "phalcon:secret@dev.phalcon.ld:8080";
        $actual   = $uri->getAuthority();
        $I->assertSame($expected, $actual);

        $expected = "dev.phalcon.ld";
        $actual   = $uri->getHost();
        $I->assertSame($expected, $actual);

        $expected = 8080;
        $actual   = $uri->getPort();
        $I->assertSame($expected, $actual);

        $expected = "/action";
        $actual   = $uri->getPath();
        $I->assertSame($expected, $actual);

        $expected = "param=value";
        $actual   = $uri->getQuery();
        $I->assertSame($expected, $actual);

        $expected = "newspaper";
        $actual   = $uri->getFragment();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - individually
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriConstructIndividually(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - __construct() - individually');

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
        $I->assertSame($expected, $actual);

        $expected = "phalcon:secret";
        $actual   = $uri->getUserInfo();
        $I->assertSame($expected, $actual);

        $expected = "phalcon:secret@dev.phalcon.ld:8080";
        $actual   = $uri->getAuthority();
        $I->assertSame($expected, $actual);

        $expected = "dev.phalcon.ld";
        $actual   = $uri->getHost();
        $I->assertSame($expected, $actual);

        $expected = 8080;
        $actual   = $uri->getPort();
        $I->assertSame($expected, $actual);

        $expected = "/action";
        $actual   = $uri->getPath();
        $I->assertSame($expected, $actual);

        $expected = "param=value";
        $actual   = $uri->getQuery();
        $I->assertSame($expected, $actual);

        $expected = "newspaper";
        $actual   = $uri->getFragment();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __construct() - malformed uri
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function httpMessageUriConstructMalformedUri(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Uri - __construct() - malformed');

        $I->expectThrowable(
            new InvalidArgumentException('The URI cannot be parsed'),
            function () {
                $uri = new Uri('https://');
            }
        );
    }
}
