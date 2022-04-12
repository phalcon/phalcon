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

namespace Phalcon\Tests\Unit\Http\Message\Response;

use Phalcon\Http\Message\Response;
use UnitTester;

class WithStatusCest
{
    /**
     * Tests Phalcon\Http\Message\Response :: withStatus()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseWithStatus(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Response - withStatus()');

        $code        = 420;
        $response    = new Response();
        $newInstance = $response->withStatus($code);

        $I->assertNotSame($response, $newInstance);

        $expected = $code;
        $actual   = $newInstance->getStatusCode();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Response :: withStatus() - other reason
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function httpMessageResponseWithStatusOtherReason(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Response - withStatus() - other reason');

        $code        = 420;
        $reason      = 'Phalcon Response';
        $response    = new Response();
        $newInstance = $response->withStatus($code, $reason);

        $I->assertNotSame($response, $newInstance);

        $expected = $code;
        $actual   = $newInstance->getStatusCode();
        $I->assertSame($expected, $actual);

        $expected = $reason;
        $actual   = $newInstance->getReasonPhrase();
        $I->assertSame($expected, $actual);
    }
}
