<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Signer\None;

use Phalcon\Encryption\Security\JWT\Signer\None;
use Phalcon\Tests\AbstractUnitTestCase;

final class SignTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Signer\None :: sign()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTSignerNoneSign(): void
    {
        $signer = new None();

        $payload    = 'test payload';
        $passphrase = '12345';

        $actual = $signer->sign($payload, $passphrase);
        $this->assertEmpty($actual);
    }
}
