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

final class ValidateAudienceTest extends UnitTestCase
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator ::
     * validateAudience()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEncryptionSecurityJWTValidatorValidateAudience(): void
    {
        $token     = $this->newToken();
        $validator = new Validator($token);

        $validator->validateAudience('unknown');

        $expected = ["Validation: audience not allowed"];
        $actual   = $validator->getErrors();
        $this->assertSame($expected, $actual);
    }
}
