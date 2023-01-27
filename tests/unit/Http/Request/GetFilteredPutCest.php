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
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function file_put_contents;

class GetFilteredPutCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getFilteredPut()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetFilteredPut(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getFilteredPut()');

        $this->registerStream();

        file_put_contents(Http::STREAM, 'no-id=24');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PUT;

        $request = $this->getRequestObject();
        $request
            ->setParameterFilters('id', ['absint'], ['put'])
        ;

        $expected = 24;
        $actual   = $request->getFilteredPut('id', 24);
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }
}
