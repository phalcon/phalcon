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

namespace Phalcon\Tests\Unit\Encryption\Security;

use Codeception\Example;
use Phalcon\Encryption\Security;
use Phalcon\Tests\UnitTestCase;

final class ComputeHmacTest extends UnitTestCase
{
    /**
     * Tests the HMAC computation
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     *
     * @dataProvider hmacProvider
     */
    public function testEncryptionSecurityComputeHmac(string $text): void
    {
        $security = new Security();

        $keys = [
            substr(md5('test', true), 0, strlen(md5('test', true)) / 2),
            md5('test', true),
            md5('test', true) . md5('test', true),
        ];

        $this->assertSame(
            hash_hmac('md5', $text, $keys[0]),
            $security->computeHmac($text, $keys[0], 'md5')
        );

        $this->assertSame(
            hash_hmac('md5', $text, $keys[1]),
            $security->computeHmac($text, $keys[1], 'md5')
        );

        $this->assertSame(
            hash_hmac('md5', $text, $keys[2]),
            $security->computeHmac($text, $keys[2], 'md5')
        );
    }

    /**
     * @return array
     */
    public static function hmacProvider(): array
    {
        $data = [];

        for ($i = 1; $i < 256; ++$i) {
            $data[] = [
                str_repeat('a', $i),
            ];
        }

        return $data;
    }
}
