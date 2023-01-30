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

use Page\Http;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function file_put_contents;
use function parse_str;

class GetRawBodyCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getRawBody() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetRawBodyDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getRawBody() - default');

        // Empty
        $request = $this->getRequestObject();

        $actual = $request->getRawBody();
        $I->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getRawBody()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetRawBody(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getRawBody()');

        // Valid
        $this->registerStream();

        file_put_contents(Http::STREAM, 'fruit=orange&quantity=4');

        $request = $this->getRequestObject();

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];

        $data = $request->getRawBody();
        parse_str($data, $actual);

        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }
}
