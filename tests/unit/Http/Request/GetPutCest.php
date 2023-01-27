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
use Phalcon\Http\Request;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function parse_str;

class GetPutCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getPut()
     *
     * @issue  @13418
     * @author Phalcon Team <team@phalcon.io>
     * @since  2017-06-03
     */
    public function httpRequestGetPut(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPut()');

        $this->registerStream();

        file_put_contents(Http::STREAM, 'fruit=orange&quantity=4');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PUT;

        $request = $this->getRequestObject();

        $actual = $request->hasPut('fruit');
        $I->assertTrue($actual);

        $actual = $request->hasPut('quantity');
        $I->assertTrue($actual);

        $actual = $request->hasPut('unknown');
        $I->assertFalse($actual);

        $data = file_get_contents(Http::STREAM);

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];

        $actual = [];
        parse_str($data, $actual);

        $I->assertSame($expected, $actual);

        $actual = $request->getPut();
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }

    /**
     * Tests Phalcon\Http\Request :: getPut() - json
     *
     * @issue  @13418
     * @author Phalcon Team <team@phalcon.io>
     * @since  2017-06-03
     */
    public function httpRequestGetPutJson(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPut() - json');

        $this->registerStream();

        file_put_contents(
            Http::STREAM,
            '{"fruit": "orange", "quantity": "4"}'
        );

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PUT;
        $_SERVER['CONTENT_TYPE']   = Http::CONTENT_TYPE_JSON;

        $request = $this->getRequestObject();

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];

        $actual = json_decode(
            file_get_contents(Http::STREAM),
            true
        );

        $I->assertSame($expected, $actual);

        $actual = $request->getPut();
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }
}
