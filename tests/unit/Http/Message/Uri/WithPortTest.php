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

use function sprintf;

final class WithPortTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'https',
                null,
                null,
                '',
            ],
            [
                'http',
                8080,
                8080,
                ':8080',
            ],
            [
                'http',
                80,
                null,
                '',
            ],
            [
                'https',
                443,
                null,
                '',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function getExceptions(): array
    {
        return [
            [
                -2,
            ],
            [
                70000,
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function testHttpMessageUriWithPort(
        string $scheme,
        ?int $source,
        ?int $expected,
        string $toString
    ): void {
        $query = '://phalcon:secret@dev.phalcon.ld%s/action?param=value#frag';
        $uri   = new Uri($scheme . sprintf($query, ':4300'));

        /**
         * New Instance
         */
        $newInstance = $uri->withPort($source);
        $this->assertNotSame($uri, $newInstance);

        /**
         * Same instance
         */
        $sameInstance = $newInstance->withPort($source);
        $this->assertSame($newInstance, $sameInstance);

        $actual = $newInstance->getPort();
        $this->assertSame($expected, $actual);

        $expected = $scheme . sprintf($query, $toString);
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort() - exception no string
     *
     * @dataProvider getExceptions
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-07
     */
    public function testHttpUriWithPortException(
        int $source
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid port specified. (Valid range 1-65535)'
        );

        $query = 'https://phalcon:secret@dev.phalcon.ld%s/action?param=value#frag';
        $uri   = new Uri(sprintf($query, ':4300'));

        $uri->withPort($source);
    }
}
