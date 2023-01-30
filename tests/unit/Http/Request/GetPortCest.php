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
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetPortCest extends HttpBase
{
    /**
     * Tests Request::getPort
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2016-06-26
     */
    public function testHttpRequestPort(UnitTester $I, Example $example)
    {
        $label    = $example['label'];
        $https    = $example['https'];
        $host     = $example['host'];
        $expected = $example['expected'];

        $I->wantToTest('Http\Request - getPort() - ' . $label);

        $request = $this->getRequestObject();

        $_SERVER['HTTPS']     = $https;
        $_SERVER['HTTP_HOST'] = $host;

        if ('unset' === $https) {
            unset($_SERVER['https']);
        }

        $actual = $request->getPort();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'label'    => 'https on',
                'https'    => 'on',
                'host'     => Http::TEST_DOMAIN,
                'expected' => 443,
            ],
            [
                'label'    => 'https off',
                'https'    => 'off',
                'host'     => Http::TEST_DOMAIN,
                'expected' => 80,
            ],
            [
                'label'    => 'https off custom port',
                'https'    => 'off',
                'host'     => Http::TEST_DOMAIN . ':8080',
                'expected' => 8080,
            ],
            [
                'label'    => 'https on custom port',
                'https'    => 'on',
                'host'     => Http::TEST_DOMAIN . ':8081',
                'expected' => 8081,
            ],
            [
                'label'    => 'only host custom port',
                'https'    => 'unset',
                'host'     => Http::TEST_DOMAIN . ':8082',
                'expected' => 8082,
            ],
        ];
    }
}
