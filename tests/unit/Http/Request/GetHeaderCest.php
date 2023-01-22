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

namespace Phalcon\Tests\Unit\Http\Request;

use Phalcon\Http\Request;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function uniqid;

class GetHeaderCest extends HttpBase
{
    /**
     * Tests getHeader empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestHeaderGetEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getHeader() - empty');

        $request = new Request();

        $name = uniqid('name-');
        $actual = $request->getHeader($name);
        $I->assertEmpty($actual);
    }

    /**
     * Tests getHeader
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestHeaderGet(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getHeader() - empty');

        $value = uniqid('val-');
        $_SERVER['HTTP_ABCDEF'] = $value;

        $request = new Request();

        $expected = $value;
        $actual   = $request->getHeader('ABCDEF');
        $I->assertSame($expected, $actual);
    }
}
