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
use Phalcon\Storage\Exception;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function strtolower;
use function uniqid;

class GetPostCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getPost()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-01
     */
    public function httpRequestGetPost(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPost()');

        $key     = uniqid('key-');
        $value   = uniqid('val-');
        $unknown = uniqid('unknown-');

        $_POST[$key] = $value;

        $request = $this->getRequestObject();

        $actual = $request->hasPost($key);
        $I->assertTrue($actual);

        $actual = $request->hasPost($unknown);
        $I->assertFalse($actual);

        $expected = $value;
        $actual   = $request->getPost($key);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getPost() - filter
     *
     * @throws Exception
     * @since  2019-12-01
     * @author Phalcon Team <team@phalcon.io>
     */
    public function httpRequestGetPostFilter(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPost() - filter');

        $key = uniqid('key-');
        $value = uniqid('VAL-');

        $_POST[$key] = '  ' . $value . '  ';

        $request = $this->getRequestObject();

        $expected = $value;
        $actual   = $request->getPost($key, 'trim');
        $I->assertSame($expected, $actual);

        $expected = strtolower($value);
        $actual   = $request->getPost($key, ['trim', 'lower']);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getPost() - default
     *
     * @throws Exception
     * @since  2019-12-01
     * @author Phalcon Team <team@phalcon.io>
     */
    public function httpRequestGetPostDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPost() - default');

        $unknown = uniqid('unknown-');
        $default = uniqid('def-');

        $request = $this->getRequestObject();

        $expected = $default;
        $actual   = $request->getPost($unknown, null, $default);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getPost() - allowNoEmpty
     *
     * @throws Exception
     * @since  2019-12-01
     * @author Phalcon Team <team@phalcon.io>
     */
    public function httpRequestGetPostAllowNoEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPost() - allowNoEmpty');

        $key   = uniqid('key-');
        $value = '0';

        $_POST[$key] = '  ' . $value . '  ';

        $request = $this->getRequestObject();

        $expected = $value;
        $actual   = $request->getPost(
            $key,
            'trim',
            'zero value',
            true
        );
        $I->assertSame($expected, $actual);
    }
}
