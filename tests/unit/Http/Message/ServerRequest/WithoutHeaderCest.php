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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Page\Http;
use Phalcon\Http\Message\ServerRequest;
use UnitTester;

class WithoutHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withoutHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithoutHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - withoutHeader()');
        $data        = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $request     = new ServerRequest('GET', null, [], Http::STREAM, $data);
        $newInstance = $request->withoutHeader('Accept');

        $I->assertNotSame($request, $newInstance);

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $request->getHeaders();
        $I->assertSame($expected, $actual);

        $expected = [
            'Cache-Control' => ['max-age=0'],
        ];
        $actual   = $newInstance->getHeaders();
        $I->assertSame($expected, $actual);
    }
}
