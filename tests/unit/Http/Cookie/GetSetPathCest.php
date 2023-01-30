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

namespace Phalcon\Tests\Unit\Http\Cookie;

use Phalcon\Http\Cookie;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetSetPathCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Cookie :: getPath()/setPath()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function httpCookieGetSetPath(UnitTester $I)
    {
        $I->wantToTest('Http\Cookie - getPath()/setPath()');

        $this->setDiService('sessionStream');

        $path   = "/";
        $cookie = $this->getCookieObject();

        $expected = $path;
        $actual   = $cookie->getPath();
        $I->assertSame($expected, $actual);

        $path = '/accounting';
        $cookie->setPath($path);

        $expected = $path;
        $actual   = $cookie->getPath();
        $I->assertSame($expected, $actual);
    }
}
