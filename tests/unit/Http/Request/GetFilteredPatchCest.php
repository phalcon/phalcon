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

class GetFilteredPatchCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getFilteredPatch()
     *
     * @issue  16188
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-11-01
     */
    public function httpRequestGetFilteredPatch(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getFilteredPatch()');

        $this->registerStream();

        file_put_contents(Http::STREAM, 'no-id=24');

        $_SERVER['REQUEST_METHOD'] = Http::METHOD_PATCH;

        $request = $this->getRequestObject();
        $request
            ->setParameterFilters('id', ['absint'], ['patch'])
        ;

        $expected = 24;
        $actual   = $request->getFilteredPut('id', 24);
        $I->assertSame($expected, $actual);

        $this->unregisterStream();
    }
}
