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

final class WithQueryTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                'p^aram',
                'p%5Earam',
            ],
            [
                'p^aram=valu`',
                'p%5Earam=valu%60',
            ],
            [
                'param[]',
                'param%5B%5D',
            ],
            [
                'param[]=valu`',
                'param%5B%5D=valu%60',
            ],
            [
                '?param=valu',
                'param=valu',
            ],
            [
                'p^aram&all[]=va lu`&param<>=`test',
                'p%5Earam&all%5B%5D=va%20lu%60&param%3C%3E=%60test',
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageUriWithQuery(
        string $source,
        string $expected
    ): void {
        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?%s#frag';
        $uri   = new Uri(sprintf($query, 'param=value'));

        $newInstance = $uri->withQuery($source);

        $this->assertNotSame($uri, $newInstance);

        $actual = $newInstance->getQuery();
        $this->assertSame($expected, $actual);

        $expected = sprintf($query, $expected);
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withQuery() - exception with fragment
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-07
     */
    public function testHttpUriWithQueryExceptionWithFragment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Query cannot contain a URI fragment'
        );

        $uri = new Uri(
            'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag'
        );

        $uri->withQuery('/login#frag');
    }
}
