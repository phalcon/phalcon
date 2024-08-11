<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Validator;

use Phalcon\Encryption\Security\JWT\Validator;
use Phalcon\Tests\Fixtures\Traits\JWTTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTValidatorConstruct(): void
    {
        $token     = $this->newToken();
        $now       = strtotime("now");
        $validator = new Validator($token);
        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateAudience("my-audience")
        );
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateExpiration($now)
        );
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateId("PH-JWT")
        );
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateNotBefore($now)
        );
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateIssuedAt($now + 100)
        );
        $this->assertInstanceOf(
            Validator::class,
            $validator->validateIssuer("Phalcon JWT")
        );
    }
}
