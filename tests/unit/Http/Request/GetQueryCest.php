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

use function strtolower;
use function uniqid;

class GetQueryCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getQuery()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-01
     */
    public function httpRequestGetQuery(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getQuery()');

        $key        = uniqid('key-');
        $value      = uniqid('val-');
        $unknown    = uniqid('unk-');
        $_GET[$key] = $value;

        $request = $this->getRequestObject();

        $actual = $request->hasQuery($key);
        $I->assertTrue($actual);

        $actual = $request->hasQuery($unknown);
        $I->assertFalse($actual);

        $expected = $value;
        $actual   = $request->getQuery($key);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getQuery() - filter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-01
     */
    public function httpRequestGetQueryFilter(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getQuery() - filter');

        $key        = uniqid('key-');
        $value      = uniqid('VAL-');
        $_GET[$key] = '  ' . $value . '  ';

        $request = $this->getRequestObject();

        $expected = $value;
        $actual   = $request->getQuery($key, 'trim');
        $I->assertSame($expected, $actual);

        $expected = strtolower($value);
        $actual   = $request->getQuery($key, ['trim', 'lower']);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getQuery() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-01
     */
    public function httpRequestGetQueryDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getQuery() - default');

        $key     = uniqid('key-');
        $request = $this->getRequestObject();

        $expected = 'default';
        $actual   = $request->getQuery($key, null, 'default');
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getQuery() - allowNoEmpty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-01
     */
    public function httpRequestGetQueryAllowNoEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getQuery() - allowNoEmpty');

        $key = uniqid('key-');

        $_GET[$key] = ' 0 ';

        $request = $this->getRequestObject();

        $expected = '0';
        $actual   = $request->getQuery($key, 'trim', 'zero value', true);
        $I->assertSame($expected, $actual);
    }
}
