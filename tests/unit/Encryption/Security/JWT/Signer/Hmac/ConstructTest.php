<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Signer\Hmac;

use Phalcon\Encryption\Security\JWT\Exceptions\UnsupportedAlgorithmException;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Signer\Hmac :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTSignerHmacConstruct(): void
    {
        $signer = new Hmac();
        $this->assertInstanceOf(Hmac::class, $signer);
    }

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Signer\Hmac :: __construct()
     * - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTSignerHmacConstructException(): void
    {
        $this->expectException(UnsupportedAlgorithmException::class);
        $this->expectExceptionMessage('Unsupported HMAC algorithm');

        (new Hmac('unknown'));
    }
}
