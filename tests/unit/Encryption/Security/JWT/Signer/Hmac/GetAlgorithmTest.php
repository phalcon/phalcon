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

final class GetAlgorithmTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Signer\Hmac :: getAlgorithm()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTSignerHmacGetAlgorithm(): void
    {
        $signer = new Hmac();
        $this->assertSame('sha512', $signer->getAlgorithm());

        $signer = new Hmac('sha512');
        $this->assertSame('sha512', $signer->getAlgorithm());

        $signer = new Hmac('sha384');
        $this->assertSame('sha384', $signer->getAlgorithm());

        $signer = new Hmac('sha256');
        $this->assertSame('sha256', $signer->getAlgorithm());
    }
}
