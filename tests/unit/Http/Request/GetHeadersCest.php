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

class GetHeadersCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getHeaders()
     *
     * @issue  2294
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-10-19
     */
    public function httpRequestGetHeaders(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getHeaders()');

        $_SERVER['HTTP_FOO']     = 'Bar';
        $_SERVER['HTTP_BLA_BLA'] = 'boo';
        $_SERVER['HTTP_AUTH']    = true;

        $request = new Request();

        $expected = [
            'Foo'     => 'Bar',
            'Bla-Bla' => 'boo',
            'Auth'    => true,
        ];

        $actual = $request->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
