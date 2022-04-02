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

namespace Phalcon\Tests\Unit\Http\Message\Uri;

use Codeception\Example;
use InvalidArgumentException;
use Phalcon\Http\Message\Uri;
use UnitTester;

class WithQueryCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageUriWithQuery(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Uri - withQuery() - ' . $example['label']);

        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?%s#frag';
        $uri   = new Uri(sprintf($query, 'param=value'));

        $source      = $example['source'];
        $expected    = $example['expected'];
        $newInstance = $uri->withQuery($source);

        $I->assertNotSame($uri, $newInstance);

        $actual = $newInstance->getQuery();
        $I->assertSame($expected, $actual);

        $expected = sprintf($query, $expected);
        $actual   = (string) $newInstance;
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery() - exception with fragment
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-07
     */
    public function httpUriWithQueryExceptionWithFragment(UnitTester $I)
    {
        $I->wantToTest('Http\Uri - withQuery() - exception - with fragment');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Query cannot contain a URI fragment'
            ),
            function () {
                $uri = new Uri(
                    'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag'
                );

                $instance = $uri->withQuery('/login#frag');
            }
        );
    }

    private function getExamples(): array
    {
        return [
            [
                'label'    => 'key only',
                'source'   => 'p^aram',
                'expected' => 'p%5Earam',
            ],
            [
                'label'    => 'key and value',
                'source'   => 'p^aram=valu`',
                'expected' => 'p%5Earam=valu%60',
            ],
            [
                'label'    => 'key as array',
                'source'   => 'param[]',
                'expected' => 'param%5B%5D',
            ],
            [
                'label'    => 'key as array and value',
                'source'   => 'param[]=valu`',
                'expected' => 'param%5B%5D=valu%60',
            ],
            [
                'label'    => 'key with questionmark',
                'source'   => '?param=valu',
                'expected' => 'param=valu',
            ],
            [
                'label'    => 'complex',
                'source'   => 'p^aram&all[]=va lu`&param<>=`test',
                'expected' => 'p%5Earam&all%5B%5D=va%20lu`&param%3C%3E=%60test',
            ],
        ];
    }
}
