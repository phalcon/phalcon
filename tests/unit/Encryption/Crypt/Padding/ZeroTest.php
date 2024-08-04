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

namespace Phalcon\Tests\Unit\Encryption\Crypt\Padding;

use Phalcon\Encryption\Crypt\Padding\Zero;
use Phalcon\Tests\UnitTestCase;

use function chr;
use function str_repeat;

final class ZeroTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Encryption\Crypt\Padding\Zero :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-12
     */
    public function testEncryptionCryptPaddingZero(): void
    {
        $object = new Zero();

        $padding  = str_repeat(chr(0), 16);
        $expected = $padding;
        $actual   = $object->pad(16);
        $this->assertSame($expected, $actual);

        $source   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" . $actual;
        $expected = 16;
        $actual   = $object->unpad($source, 16);
        $this->assertSame($expected, $actual);
    }
}
