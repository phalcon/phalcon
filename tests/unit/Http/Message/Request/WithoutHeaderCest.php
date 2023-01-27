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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Page\Http;
use Phalcon\Http\Message\Request;
use UnitTester;

class WithoutHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: withoutHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithoutHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withoutHeader()');

        $data = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $request = new Request('GET', null, Http::STREAM_MEMORY, $data);

        $newInstance = $request->withoutHeader('Accept');

        $I->assertNotSame($request, $newInstance);

        $expected = [
            'Accept'        => [Http::CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $I->assertSame(
            $expected,
            $request->getHeaders()
        );

        $expected = [
            'Cache-Control' => ['max-age=0'],
        ];

        $I->assertSame(
            $expected,
            $newInstance->getHeaders()
        );
    }
}
