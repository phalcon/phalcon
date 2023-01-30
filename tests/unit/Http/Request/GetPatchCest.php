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

use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function parse_str;

class GetPatchCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getPatch()
     *
     * @issue  16188
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-11-01
     */
    public function httpRequestGetPatch(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPatch()');

        $this->registerStream();

        file_put_contents(Http::STREAM, 'fruit=orange&quantity=4');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PATCH;

        $request = $this->getRequestObject();

        $actual = $request->hasPatch('fruit');
        $I->assertTrue($actual);
        $actual = $request->hasPatch('quantity');
        $I->assertTrue($actual);
        $actual = $request->hasPatch('unknown');
        $I->assertFalse($actual);

        $data = file_get_contents(Http::STREAM);

        $actual = [];
        parse_str($data, $actual);

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];
        $I->assertSame($expected, $actual);

        $actual = $request->getPatch();
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }

    /**
     * Tests Phalcon\Http\Request :: getPatch() - json
     *
     * @issue  16188
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-11-01
     */
    public function httpRequestGetPatchJson(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getPatch() - json');

        $this->registerStream();

        file_put_contents(
            Http::STREAM,
            '{"fruit": "orange", "quantity": "4"}'
        );

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PATCH;
        $_SERVER['CONTENT_TYPE']   = Http::CONTENT_TYPE_JSON;

        $request = $this->getRequestObject();

        $expected = [
            'fruit'    => 'orange',
            'quantity' => '4',
        ];
        $actual   = json_decode(
            file_get_contents(Http::STREAM),
            true
        );
        $I->assertSame($expected, $actual);

        $actual = $request->getPatch();
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }
}
