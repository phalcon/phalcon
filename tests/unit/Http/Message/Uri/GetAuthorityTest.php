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

final class GetAuthorityTest extends AbstractUnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                '',
                '',
            ],

            [
                'https://dev.phalcon.ld',
                'dev.phalcon.ld',
            ],

            [
                'https://phalcon:secret@dev.phalcon.ld',
                'phalcon:secret@dev.phalcon.ld',
            ],

            [
                'https://dev.phalcon.ld:8080',
                'dev.phalcon.ld:8080',
            ],

            [
                'https://phalcon:secret@dev.phalcon.ld:8080',
                'phalcon:secret@dev.phalcon.ld:8080',
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: getAuthority()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageUriGetAuthority(
        string $uriStr,
        string $expected
    ): void {
        $uri = new Uri($uriStr);

        $this->assertSame($expected, $uri->getAuthority());
    }
}
