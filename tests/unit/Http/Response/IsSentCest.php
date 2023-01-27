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

namespace Phalcon\Tests\Unit\Http\Response;

use Page\Http;
use Phalcon\Http\Response;
use UnitTester;

class IsSentCest
{
    /**
     * Tests Phalcon\Http\Response :: isSent()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseIsSent(UnitTester $I)
    {
        $I->wantToTest('Http\Response - isSent()');

        $content  = Http::TEST_CONTENT;
        $response = new Response($content);

        ob_start();

        $response->send();
        $result = ob_get_clean();

        $expected = $content;
        $actual   = $result;
        $I->assertSame($expected, $actual);

        $actual = $response->isSent();
        $I->assertTrue($actual);
    }
}
