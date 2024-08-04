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

namespace Phalcon\Tests\Unit\Encryption\Crypt;

use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Crypt\Exception\Exception;
use Phalcon\Tests\UnitTestCase;

final class GetSetCipherTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Encryption\Crypt :: getCipher() / setCipher()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptGetSetCipher(): void
    {
        $cipher = 'aes-256-cfb';
        $crypt  = new Crypt();
        $crypt->setCipher($cipher);

        $this->assertSame($cipher, $crypt->getCipher());
    }

    /**
     * Tests Phalcon\Encryption\Crypt :: setCipher() - unknown
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptGetSetCipherUnknown(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "The cipher algorithm 'xxx-yyy-zzz' is not supported on this system."
        );

        $crypt = new Crypt();
        $crypt->setCipher('xxx-yyy-zzz');
    }
}
