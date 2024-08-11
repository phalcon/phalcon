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
use Phalcon\Tests\Fixtures\Encryption\Crypt\CryptFunctionExistsFixture;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests the Crypt constants
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testCryptConstants(): void
    {
        $this->assertSame(0, Crypt::PADDING_DEFAULT);
        $this->assertSame(1, Crypt::PADDING_ANSI_X_923);
        $this->assertSame(2, Crypt::PADDING_PKCS7);
        $this->assertSame(3, Crypt::PADDING_ISO_10126);
        $this->assertSame(4, Crypt::PADDING_ISO_IEC_7816_4);
        $this->assertSame(5, Crypt::PADDING_ZERO);
        $this->assertSame(6, Crypt::PADDING_SPACE);
    }

    /**
     * Tests Phalcon\Encryption\Crypt :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptConstruct(): void
    {
        $crypt = new Crypt();
        $this->assertInstanceOf(Crypt::class, $crypt);

        $crypt = new Crypt('aes-256-cfb', true);
        $this->assertInstanceOf(Crypt::class, $crypt);
    }

    /**
     * Tests Phalcon\Encryption\Crypt :: __construct() - no openssl exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-10-18
     */
    public function testEncryptionCryptConstructNoOpensslException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'This class requires the openssl extension for PHP'
        );

        (new CryptFunctionExistsFixture());
    }
}
