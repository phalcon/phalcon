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

use const PHP_OS_FAMILY;

class WithPathCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withPath()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function httpMessageUriWithPath(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Uri - withPath() - ' . $example['label']);

        if (PHP_OS_FAMILY !== 'Linux') {
            $I->markTestSkipped('Need to check the UTF8 on Mac/Win');
        }

        $source   = $example['url'];
        $path     = $example['path'];
        $expected = $example['expected'];
        $toString = $example['toString'];
        $uri      = new Uri($source);

        $newInstance = $uri->withPath($path);

        $I->assertNotSame($uri, $newInstance);

        $actual = $newInstance->getPath();
        $I->assertSame($expected, $actual);

        $expected = $toString;
        $actual   = (string) $newInstance;
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPath() - exception query fragment
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function httpUriWithPathExceptionQueryFragment(UnitTester $I)
    {
        $I->wantToTest('Http\Uri - withPath() - exception - query fragment');

        $I->expectThrowable(
            new InvalidArgumentException(
                'Path cannot contain a query string or fragment'
            ),
            function () {
                $query    = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag';
                $uri      = new Uri($query);
                $instance = $uri->withPath('/login#frag');
            }
        );
    }

    /**
     * @return string[][]
     */
    private function getExamples(): array
    {
        $url = 'https://dev.phalcon.ld';

        return [
            [
                'label'    => 'empty',
                'url'      => $url . '/action',
                'path'     => '',
                'expected' => '',
                'toString' => $url . '',
            ],
            [
                'label'    => 'normal',
                'url'      => $url . '/action',
                'path'     => '/login',
                'expected' => '/login',
                'toString' => $url . '/login',
            ],
            [
                'label'    => 'double slash',
                'url'      => $url . '/action',
                'path'     => '//login',
                'expected' => '/login',
                'toString' => $url . '/login',
            ],
            [
                'label'    => 'no leading slash',
                'url'      => $url . '/action',
                'path'     => 'login',
                'expected' => 'login',
                'toString' => $url . '/login',
            ],
            [
                'label'    => 'garbled',
                'url'      => $url . '/action',
                'path'     => '/l^ogin/si gn',
                'expected' => '/l%5Eogin/si%20gn',
                'toString' => $url . '/l%5Eogin/si%20gn',
            ],
            [
                'label'    => 'with double colon',
                'url'      => $url . '/action;parameter?query:fragment',
                'path'     => 'action;parameter',
                'expected' => 'action;parameter',
                'toString' => $url . '/action;parameter?query:fragment',
            ],
            [
                'label'    => 'utf8 russian',
                'url'      => $url . '/action?параметр=ценность#фрагмент',
                'path'     => 'действие',
                'expected' => 'действие',
                'toString' => $url . '/действие?параметр=ценность#фрагмент',
            ],
            [
                'label'    => 'utf8 greek',
                'url'      => $url . '/action?παράμετρος=ερώτηση#θραύσμα',
                'path'     => 'ενέργεια',
                'expected' => 'ενέργεια',
                'toString' => $url . '/ενέργεια?παράμετρος=ερώτηση#θραύσμα',
            ],
        ];
    }
}
