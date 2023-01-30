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

use function ucfirst;

class GetFilteredPostCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getFilteredPost()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-01
     */
    public function httpRequestGetFilteredPost1(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Request - getFiltered*() - ' . $example[0]);

        $request = $this->getRequestObject();

        $request->setParameterFilters($example[1], $example[2], $example[3]);

        if ('query' === $example[0]) {
            $_GET = [
                $example[1] => $example[4],
            ];
        } elseif ('post' === $example[0]) {
            $_POST = [
                $example[1] => $example[4],
            ];
        }

        $method   = 'getFiltered' . ucfirst($example[0]);
        $expected = $example[5];
        $actual   = $request->$method($example[1]);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Request :: getFilteredPost() - default
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-01
     */
    public function httpRequestGetFilteredDefault(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getFiltered*() - default');

        $request = $this->getRequestObject();
        $request
            ->setParameterFilters('id', ['absint'], ['post', 'get'])
        ;

        $_GET  = ['no-id' => '24'];
        $_POST = ['no-id' => '24'];

        $expected = 24;
        $actual   = $request->getFilteredQuery('id', 24);
        $I->assertSame($expected, $actual);

        $expected = 24;
        $actual   = $request->getFilteredPost('id', 24);
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'post',
                'id',
                ['absint'],
                ['post'],
                '24',
                24,
            ],
            [
                'post',
                'name',
                ['striptags', 'trim'],
                ['post'],
                ' This is a <strong>test</strong>   ',
                'This is a test',
            ],
            [
                'query',
                'id',
                ['absint'],
                ['get'],
                '24',
                24,
            ],
            [
                'query',
                'name',
                ['striptags', 'trim'],
                ['get'],
                ' This is a <strong>test</strong>   ',
                'This is a test',
            ],
        ];
    }
}
