<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Builder;

use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Encryption\Security\JWT\Token\Token;
use Phalcon\Tests\Fixtures\Traits\JWTTrait;
use Phalcon\Tests\UnitTestCase;

final class GetTokenTest extends UnitTestCase
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: getToken()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetToken(): void
    {
        $token = $this->newToken();

        $this->assertInstanceOf(Token::class, $token);

        $parts = explode('.', $token->getToken());
        $this->assertCount(3, $parts);
    }

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: getToken() -
     * exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTBuilderGetTokenException(): void
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage("Invalid passphrase (empty)");

        $signer = new Hmac();
        $builder = new Builder($signer);
        $builder->getToken();
    }
}
