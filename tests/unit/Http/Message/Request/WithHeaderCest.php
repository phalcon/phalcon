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
use Codeception\Example;
use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Request;
use UnitTester;

class WithHeaderCest
{
    /**
     * Tests Phalcon\Http\Message\Request :: withHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithHeader(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withHeader()');

        $data = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];

        $request = new Request('GET', null, Http::STREAM_MEMORY, $data);

        $newInstance = $request->withHeader(
            'Cache-Control',
            [
                'max-age=0',
            ]
        );

        $I->assertNotSame($request, $newInstance);

        $expected = [
            'Accept' => [Http::HEADERS_CONTENT_TYPE_HTML],
        ];

        $I->assertSame(
            $expected,
            $request->getHeaders()
        );

        $expected = [
            'Accept'        => [Http::HEADERS_CONTENT_TYPE_HTML],
            'Cache-Control' => ['max-age=0'],
        ];

        $I->assertSame(
            $expected,
            $newInstance->getHeaders()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withHeader() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageRequestWithHeaderException(UnitTester $I)
    {
        $I->wantToTest('Http\Message\Request - withHeader() value');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Invalid header name Cache Control'
            ),
            function () {
                $request = new Request();

                $newInstance = $request->withHeader(
                    'Cache Control',
                    [
                        'max-age=0',
                    ]
                );
            }
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: withHeader() - exception value
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-10
     */
    public function httpMessageRequestWithHeaderExceptionValue(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Request - withHeader() - exception value ' . $example[0]);

        $I->expectThrowable(
            new InvalidArgumentException(
                'Invalid header value'
            ),
            function () use ($example) {
                $request = new Request();

                $newInstance = $request->withHeader(
                    'Cache-Control',
                    [
                        $example[1],
                    ]
                );
            }
        );
    }


    private function getExamples(): array
    {
        return [
            ['not numeric or string', true],
            ['invalid\r\n', "some \r\n"],
            ['invalid\r', "some \r"],
            ['invalid\n', "some \n"],
        ];
    }
}
