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

use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

use function sprintf;

final class WithUserInfoTest extends AbstractUnitTestCase
{
    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        return [
            [
                'phalcon',
                'secret',
                'phalcon:secret',
            ],
            [
                'phalcon',
                null,
                'phalcon',
            ],
            [
                'phalcon',
                '',
                'phalcon:',
            ],
            [
                'phalcon@secret',
                'secret@phalcon',
                'phalcon%40secret:secret%40phalcon',
            ],
            [
                'phalcon:secret',
                'secret:phalcon',
                'phalcon%3Asecret:secret%3Aphalcon',
            ],
            [
                'phalcon%secret',
                'secret%phalcon',
                'phalcon%25secret:secret%25phalcon',
            ],
            [
                "\x21\x92",
                '!?',
                '!%92:!%3F',
            ],
            [
                '%ZZ',
                '%GG',
                '%25ZZ:%25GG',
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withUserInfo()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageUriWithUserInfo(
        string $user,
        ?string $pass,
        string $expected
    ): void {
        $query = 'https://%s@dev.phalcon.ld:8080/action?param=value#frag';
        $uri   = new Uri(sprintf($query, 'zephir:module'));

        $newInstance = $uri->withUserInfo($user, $pass);
        $this->assertNotSame($uri, $newInstance);

        $actual = $newInstance->getUserInfo();
        $this->assertSame($expected, $actual);

        $expected = sprintf($query, $expected);
        $actual   = (string)$newInstance;
        $this->assertSame($expected, $actual);
    }
}
