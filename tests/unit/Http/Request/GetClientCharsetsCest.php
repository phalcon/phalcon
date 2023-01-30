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

class GetClientCharsetsCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getClientCharsets()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetClientCharsets(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getClientCharsets()');

        $_SERVER['HTTP_ACCEPT_CHARSET'] = 'iso-8859-5,unicode-1-1;q=0.8';

        $request = new Request();

        $expected = [
            [
                'charset' => 'iso-8859-5',
                'quality' => 1.0,
            ],
            [
                'charset' => 'unicode-1-1',
                'quality' => 0.8,
            ],
        ];
        $actual   = $request->getClientCharsets();
        $I->assertSame($expected, $actual);
    }
}
