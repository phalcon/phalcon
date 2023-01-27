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

class GetReasonPhraseCest
{
    /**
     * Tests Phalcon\Http\Response :: getReasonPhrase()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseGetReasonPhrase(UnitTester $I)
    {
        $I->wantToTest('Http\Response - getReasonPhrase()');

        $content = Http::TEST_CONTENT;
        $code    = Http::CODE_200;
        $phrase  = 'Success';

        $response = new Response($content, $code, $phrase);

        $expected = $phrase;
        $actual   = $response->getReasonPhrase();
        $I->assertSame($expected, $actual);
    }
}
