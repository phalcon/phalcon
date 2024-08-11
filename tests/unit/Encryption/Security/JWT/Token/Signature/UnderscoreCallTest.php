<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Token\Signature;

use Phalcon\Encryption\Security\JWT\Token\Signature;
use Phalcon\Tests\AbstractUnitTestCase;

final class UnderscoreCallTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Token\Signature :: __call()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTTokenSignatureUnderscoreCall(): void
    {
        $signature = new Signature('sig-hash', 'encoded-string-here');

        $this->assertSame('encoded-string-here', $signature->getEncoded());
        $this->assertSame('sig-hash', $signature->getHash());
    }
}
