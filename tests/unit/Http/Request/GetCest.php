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

use function uniqid;

class GetCest extends HttpBase
{
    /**
     * Tests get() from $_REQUEST
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     * @issue https://github.com/phalcon/cphalcon/issues/1265
     */
    public function testHttpRequestGet(UnitTester $I)
    {
        $key   = uniqid('key-');
        $value = uniqid('val-');

        $_REQUEST = [
            'id'         => 1,
            'num'        => 'a1a',
            'age'        => 'aa',
            'phone'      => '',
            'string-key' => 'hello',
            'array-key'  => ['string' => 'world'],
        ];

        $request = $this->getRequestObject();

        $actual = $request->has($key);
        $I->assertFalse($actual);

        $_REQUEST[$key] = $value;

        $actual = $request->has($key);
        $I->assertTrue($actual);

        $expected = $value;
        $actual   = $request->get($key);
        $I->assertSame($expected, $actual);

        /**
         * Get - different methods
         */
        $expected = 'hello';
        $actual   = $request->get('string-key', 'string');
        $I->assertSame($expected, $actual);

        $expected = 'hello';
        $actual   = $request->get(
            'string-key',
            'string',
            null,
            true,
            true
        );
        $I->assertSame($expected, $actual);

        $expected = ['string' => 'world'];
        $actual   = $request->get('array-key', 'string');
        $I->assertSame($expected, $actual);

        $expected = ['string' => 'world'];
        $actual   = $request->get(
            'array-key',
            'string',
            null,
            true,
            false
        );
        $I->assertSame($expected, $actual);

        $expected = 1;
        $actual = $request->get('id', 'int', 100);
        $I->assertSame($expected, $actual);

        $expected = 1;
        $actual = $request->get('num', 'int', 100);
        $I->assertSame($expected, $actual);

        $actual = $request->get('age', 'int', 100);
        $I->assertEmpty($actual);

        $actual = $request->get('phone', 'int', 100);
        $I->assertEmpty($actual);

        $expected = 100;
        $actual   = $request->get('phone', 'int', 100, true);
        $I->assertSame($expected, $actual);
    }
}
