<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http\Response;

use Page\Http;
use Phalcon\Http\Response;
use UnitTester;

class GetSetContentCest
{
    /**
     * Tests Phalcon\Http\Response :: getContent() / setContent()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-08
     */
    public function httpResponseGetSetContent(UnitTester $I)
    {
        $I->wantToTest('Http\Response - getContent() / setContent()');

        $content = Http::TEST_CONTENT;

        $response = new Response();

        $expected = '';
        $actual   = $response->getContent();
        $I->assertSame($expected, $actual);

        $response->setContent($content);

        $expected = $content;
        $actual   = $response->getContent();
        $I->assertSame($expected, $actual);
    }
}
