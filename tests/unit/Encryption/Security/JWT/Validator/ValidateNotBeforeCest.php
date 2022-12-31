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
use UnitTester;

/**
 * Class ValidateNotBeforeCest
 *
 * @package Phalcon\Tests\Unit\Encryption\Security\JWT\Validator
 */
class ValidateNotBeforeCest
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator ::
     * validateNotBefore()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityJWTValidatorValidateNotBefore(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security\JWT\Validator - validateNotBefore()');

        $token     = $this->newToken();
        $timestamp = strtotime(("-2 days"));
        $validator = new Validator($token);
        $I->assertInstanceOf(Validator::class, $validator);

        $validator->validateNotBefore($timestamp);

        $expected = ["Validation: the token cannot be used yet (not before)"];
        $actual   = $validator->getErrors();
        $I->assertSame($expected, $actual);
    }
}
