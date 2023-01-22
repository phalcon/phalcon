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

class GetContentTypeCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getContentType()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetContentType(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getContentType()');

        $_SERVER['CONTENT_TYPE'] = Http::HEADERS_CONTENT_TYPE_XHTML_XML;

        $request = new Request();

        $expected = Http::HEADERS_CONTENT_TYPE_XHTML_XML;
        $actual   = $request->getContentType();
        $I->assertSame($expected, $actual);
    }
}
