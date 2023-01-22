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

use Codeception\Example;
use Page\Http;
use Phalcon\Http\Request;
use UnitTester;

use function uniqid;

class IsValidHttpMethodCest
{
    /**
     * Tests Phalcon\Http\Request :: isValidHttpMethod()
     *
     * @dataProvider getExamples
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestIsValidHttpMethod(UnitTester $I, Example $example)
    {
        $method = $example[0];

        $I->wantToTest('Http\Request - isValidHttpMethod() - ' . $method);

        $request = new Request();

        $expected = $example[1];
        $actual   = $request->isValidHttpMethod($method);
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                Http::REQUEST_METHOD_CONNECT,
                true,
            ],
            [
                Http::REQUEST_METHOD_DELETE,
                true,
            ],
            [
                Http::REQUEST_METHOD_GET,
                true,
            ],
            [
                Http::REQUEST_METHOD_HEAD,
                true,
            ],
            [
                Http::REQUEST_METHOD_OPTIONS,
                true,
            ],
            [
                Http::REQUEST_METHOD_PATCH,
                true,
            ],
            [
                Http::REQUEST_METHOD_POST,
                true,
            ],
            [
                Http::REQUEST_METHOD_PURGE,
                true,
            ],
            [
                Http::REQUEST_METHOD_PUT,
                true,
            ],
            [
                Http::REQUEST_METHOD_TRACE,
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_CONNECT),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_DELETE),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_GET),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_HEAD),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_OPTIONS),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_PATCH),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_POST),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_PURGE),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_PUT),
                true,
            ],
            [
                strtolower(Http::REQUEST_METHOD_TRACE),
                true,
            ],
            [
                uniqid('meth-'),
                false,
            ]
        ];
    }
}
