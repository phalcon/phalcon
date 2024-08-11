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

use InvalidArgumentException;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

use const PHP_OS_FAMILY;

final class WithPathTest extends AbstractUnitTestCase
{
    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        $url = 'https://dev.phalcon.ld';

        return [
            [
                $url . '/action',
                '',
                '',
                $url . '',
            ],
            [
                $url . '/action',
                '/login',
                '/login',
                $url . '/login',
            ],
            [
                $url . '/action',
                '//login',
                '/login',
                $url . '/login',
            ],
            [
                $url . '/action',
                'login',
                'login',
                $url . '/login',
            ],
            [
                $url . '/action',
                '/l^ogin/si gn',
                '/l%5Eogin/si%20gn',
                $url . '/l%5Eogin/si%20gn',
            ],
            [
                $url . '/action;parameter?query:fragment',
                'action;parameter',
                'action;parameter',
                $url . '/action;parameter?query:fragment',
            ],
            [
                $url . '/action?параметр=ценность#фрагмент',
                'действие',
                'действие',
                $url . '/действие?параметр=ценность#фрагмент',
            ],
            [
                $url . '/action?παράμετρος=ερώτηση#θραύσμα',
                'ενέργεια',
                'ενέργεια',
                $url . '/ενέργεια?παράμετρος=ερώτηση#θραύσμα',
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPath()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageUriWithPath(
        string $source,
        string $path,
        string $expected,
        string $toString
    ): void {
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('Need to check the UTF8 on Mac/Win');
        }

        $uri         = new Uri($source);
        $newInstance = $uri->withPath($path);

        $this->assertNotSame($uri, $newInstance);

        $actual = $newInstance->getPath();
        $this->assertSame($expected, $actual);

        $expected = $toString;
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPath() - exception query fragment
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function testHttpUriWithPathExceptionQueryFragment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Path cannot contain a query string or fragment'
        );

        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag';
        $uri   = new Uri($query);
        $uri->withPath('/login#frag');
    }
}
