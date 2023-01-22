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

class GetServerCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getServer()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetServer(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getServer()');

        $key = uniqid('key-');
        $value = uniqid('val-');
        $unknown = uniqid('unk-');
        $_SERVER[$key] = $value;

        $request = $this->getRequestObject();

        $actual = $request->hasServer($key);
        $I->assertTrue($actual);

        $actual = $request->hasServer($unknown);
        $I->assertFalse($actual);

        $expected = $value;
        $actual = $request->getServer($key);
        $I->assertSame($expected, $actual);
    }
}
