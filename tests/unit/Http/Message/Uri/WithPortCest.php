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
use function sprintf;

class WithPortCest
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withPort()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function httpMessageUriWithPort(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Message\Uri - withPort() - ' . $example['label']);

        $query    = '://phalcon:secret@dev.phalcon.ld%s/action?param=value#frag';
        $scheme   = $example['scheme'];
        $source   = $example['source'];
        $expected = $example['expected'];
        $toString = $example['toString'];

        $uri   = new Uri($scheme . sprintf($query, ':4300'));


        /**
         * New Instance
         */
        $newInstance = $uri->withPort($source);
        $I->assertNotSame($uri, $newInstance);

        /**
         * Same instance
         */
        $sameInstance = $newInstance->withPort($source);
        $I->assertSame($newInstance, $sameInstance);

        $actual = $newInstance->getPort();
        $I->assertSame($expected, $actual);

        $expected = $scheme . sprintf($query, $toString);
        $actual   = (string) $newInstance;
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withPort() - exception no string
     *
     * @dataProvider getExceptions
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-07
     */
    public function httpUriWithPortException(UnitTester $I, Example $example)
    {
        $I->wantToTest('Http\Uri - withPort() - ' . $example['label']);

        $I->expectThrowable(
            new InvalidArgumentException(
                'Invalid port specified. (Valid range 1-65535)'
            ),
            function () use ($example) {
                $query = 'https://phalcon:secret@dev.phalcon.ld%s/action?param=value#frag';
                $uri   = new Uri(sprintf($query, ':4300'));

                $newInstance = $uri->withPort($example['source']);
            }
        );
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'label'    => 'null',
                'scheme'   => 'https',
                'source'   => null,
                'expected' => null,
                'toString' => '',
            ],
            [
                'label'    => 'int',
                'scheme'   => 'http',
                'source'   => 8080,
                'expected' => 8080,
                'toString' => ':8080',
            ],
            [
                'label'    => 'http',
                'scheme'   => 'http',
                'source'   => 80,
                'expected' => null,
                'toString' => '',
            ],
            [
                'label'    => 'https',
                'scheme'   => 'https',
                'source'   => 443,
                'expected' => null,
                'toString' => '',
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getExceptions(): array
    {
        return [
            [
                'label'  => 'port less than 1',
                'source' => -2,
            ],
            [
                'label'  => 'port more than max',
                'source' => 70000,
            ],
        ];
    }
}
