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
use Phalcon\Tests\UnitTestCase;

final class ValidateExpirationTest extends UnitTestCase
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator ::
     * validateExpiration()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTValidatorValidateExpiration(): void
    {
        $token     = $this->newToken();
        $timestamp = strtotime(("+2 days"));
        $validator = new Validator($token);
        $this->assertInstanceOf(Validator::class, $validator);

        $validator->validateExpiration($timestamp);

        $expected = ["Validation: the token has expired"];
        $actual   = $validator->getErrors();
        $this->assertSame($expected, $actual);
    }
}
