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
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class SetContentLengthCest extends HttpBase
{
    /**
     * Tests the setContentLength
     *
     * @author Zamrony P. Juhara <zamronypj@yahoo.com>
     * @since  2016-07-18
     */
    public function testHttpResponseSetContentLength(UnitTester $I)
    {
        $response = $this->getResponseObject();
        $response->resetHeaders();
        $response->setContentLength(100);

        $headers = $response->getHeaders();

        $expected = 100;
        $actual   = (int) $headers->get(Http::CONTENT_LENGTH);
        $I->assertSame($expected, $actual);
    }
}
