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

class GetHeaderLineCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: getHeaderLine()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeaderLine(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeaderLine()');

        $data = [
            'Accept' => [
                Http::CONTENT_TYPE_HTML,
                'text/json',
            ],
        ];

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $data
        );

        $expected = 'text/html,text/json';

        $I->assertSame(
            $expected,
            $request->getHeaderLine('accept')
        );

        $I->assertSame(
            $expected,
            $request->getHeaderLine('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeaderLine() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestGetHeaderLineEmpty(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - getHeaderLine() - empty');

        $request = new Request();

        $I->assertSame(
            '',
            $request->getHeaderLine('accept')
        );
    }
}
