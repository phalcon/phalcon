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

use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetSchemeCest extends HttpBase
{
    /**
     * Tests getScheme default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetSchemeDefault(UnitTester $I)
    {
        $request = $this->getRequestObject();

        $expected = 'http';
        $actual   = $request->getScheme();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests getScheme with HTTPS
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testHttpRequestGetScheme(UnitTester $I)
    {
        $request = $this->getRequestObject();

        $_SERVER['HTTPS'] = 'on';
        $actual = $request->getScheme();

        $I->assertSame('https', $actual);
    }
}
