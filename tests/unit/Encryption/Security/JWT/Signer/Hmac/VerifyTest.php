<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Signer\Hmac;

use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Tests\AbstractUnitTestCase;

use function hash_hmac;

final class VerifyTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Signer\Hmac :: verify()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTSignerHmacVerify(): void
    {
        $signer = new Hmac();

        $payload    = 'test payload';
        $passphrase = '12345';

        $hash   = hash_hmac('sha512', $payload, $passphrase, true);
        $actual = $signer->verify($hash, $payload, $passphrase);
        $this->assertTrue($actual);
    }
}
