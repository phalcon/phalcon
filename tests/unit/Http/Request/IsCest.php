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
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class IsCest extends HttpBase
{
    /**
     * Tests Is methods
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-03-17
     *
     * @dataProvider getExamples
     */
    public function httpRequestIs(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Request - is*() - ' . $example[0]);

        $_SERVER = array_merge($_SERVER, $example[1]);

        $request = $this->getRequestObject();

        $expected = $example[2];
        $class    = $example[3];
        $actual   = $request->$class();

        $I->assertSame($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'ajax default',
                [],
                false,
                'isAjax',
            ],
            [
                'ajax',
                [
                    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                ],
                true,
                'isAjax',
            ],
            [
                'connect default',
                [],
                false,
                'isConnect',
            ],
            [
                'connect',
                [
                    'REQUEST_METHOD' => 'CONNECT',
                ],
                true,
                'isConnect',
            ],
            [
                'delete default',
                [],
                false,
                'isDelete',
            ],
            [
                'delete',
                [
                    'REQUEST_METHOD' => 'DELETE',
                ],
                true,
                'isDelete',
            ],
            [
                'get default',
                [],
                true,
                'isGet',
            ],
            [
                'get',
                [
                    'REQUEST_METHOD' => 'GET',
                ],
                true,
                'isGet',
            ],
            [
                'head default',
                [],
                false,
                'isHead',
            ],
            [
                'head',
                [
                    'REQUEST_METHOD' => 'HEAD',
                ],
                true,
                'isHead',
            ],
            [
                'options default',
                [],
                false,
                'isOptions',
            ],
            [
                'options',
                [
                    'REQUEST_METHOD' => 'OPTIONS',
                ],
                true,
                'isOptions',
            ],
            [
                'patch default',
                [],
                false,
                'isPatch',
            ],
            [
                'patch',
                [
                    'REQUEST_METHOD' => 'PATCH',
                ],
                true,
                'isPatch',
            ],
            [
                'post default',
                [],
                false,
                'isPost',
            ],
            [
                'post',
                [
                    'REQUEST_METHOD' => 'POST',
                ],
                true,
                'isPost',
            ],
            [
                'put default',
                [],
                false,
                'isPut',
            ],
            [
                'put',
                [
                    'REQUEST_METHOD' => 'PUT',
                ],
                true,
                'isPut',
            ],
            [
                'purge default',
                [],
                false,
                'isPurge',
            ],
            [
                'purge',
                [
                    'REQUEST_METHOD' => 'PURGE',
                ],
                true,
                'isPurge',
            ],
            [
                'secure default',
                [],
                false,
                'isSecure',
            ],
            [
                'secure',
                [
                    'HTTPS' => 'on',
                ],
                true,
                'isSecure',
            ],
            [
                'soap default',
                [],
                false,
                'isSoap',
            ],
            [
                'soap',
                [
                    'CONTENT_TYPE' => 'application/soap+xml',
                ],
                true,
                'isSoap',
            ],
        ];
    }
}
