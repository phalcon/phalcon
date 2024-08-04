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

namespace Phalcon\Tests\Unit\Http\Response\Cookies;

use Phalcon\Http\Response\Cookies;
use Phalcon\Tests\Fixtures\Traits\CookieTrait;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class SendTest extends HttpBase
{
    use CookieTrait;

    /**
     * executed before each test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setDiService('sessionStream');
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: send()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-06
     */
    public function testHttpResponseCookiesSend(): void
    {
        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $actual = $cookies->send();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Http\Response\Cookies :: send() - twice
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-22
     * @issue  15334
     */
    public function testHttpResponseCookiesSendTwice(): void
    {
        $name  = uniqid('nam-');
        $value = uniqid('val-');

        $this->setDiService('crypt');

        $cookies = new Cookies();
        $cookies->setDI($this->container);
        $cookies->set($name, $value);

        $actual = $cookies->isSent();
        $this->assertFalse($actual);

        $actual = $cookies->send();
        $this->assertTrue($actual);

        $actual = $cookies->isSent();
        $this->assertTrue($actual);

        $actual = $cookies->send();
        $this->assertFalse($actual);
    }
}
