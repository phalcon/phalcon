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

class GetPreferredIsoLocaleVariantCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getPreferredIsoLocaleVariant()
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @dataProvider getExamples
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function httpRequestGetBestLanguageIso(
        UnitTester $I,
        Example $example
    ) {
        $I->wantToTest(
            'Http\Request - getPreferredIsoLocaleVariant() ' . $example['message']
        );

        $request = $this->container->get('request');

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $example['header'];

        $expected = $example['result'];
        $actual   = $request->getPreferredIsoLocaleVariant();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    private function getExamples(): array
    {
        return [
            [
                'message' => 'complex',
                'header'  => 'es,es-ar;q=0.8',
                'result'  => 'es',
            ],
            [
                'message' => 'with quality without short variant',
                'header'  => 'es-ar;q=0.8',
                'result'  => 'es',
            ],
            [
                'message' => 'long variant',
                'header'  => 'en-US',
                'result'  => 'en',
            ],
            [
                'message' => 'short variant',
                'header'  => 'en',
                'result'  => 'en',
            ],
            [
                'message' => 'garbage',
                'header'  => 'some;random;string;here;q=1',
                'result'  => 'here',
            ],
            [
                'message' => 'wildcard',
                'header'  => '*',
                'result'  => '',
            ],
            [
                'message' => 'empty',
                'header'  => '',
                'result'  => '',
            ],
        ];
    }
}
