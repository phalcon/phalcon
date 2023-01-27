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

class SetEtagCest
{
    /**
     * Tests Phalcon\Http\Response :: setEtag()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function httpResponseSetEtag(UnitTester $I)
    {
        $I->wantToTest('Http\Response - setEtag()');

        $etag     = md5((string) time());
        $response = new Response();

        $response->setEtag($etag);

        $headers = $response->getHeaders();

        $expected = $etag;
        $actual   = $headers->get(Http::ETAG);
        $I->assertSame($expected, $actual);
    }
}
