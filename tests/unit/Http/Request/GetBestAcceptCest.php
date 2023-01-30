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

class GetBestAcceptCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getBestAccept()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetBestAccept(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getBestAccept()');

        $_SERVER['HTTP_ACCEPT'] = 'text/html,'
            . 'application/xhtml+xml,application/xml;q=0.9,'
            . 'image/webp,image/apng,'
            . 'language'
            . '*/*;q=0.8';

        $request = new Request();

        $expected = Http::CONTENT_TYPE_HTML;
        $actual   = $request->getBestAccept();
        $I->assertSame($expected, $actual);
    }
}
