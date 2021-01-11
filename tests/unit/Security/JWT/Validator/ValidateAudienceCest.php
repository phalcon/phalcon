<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Security\JWT\Validator;

use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Validator;
use Phalcon\Tests\Fixtures\Traits\JWTTrait;
use UnitTester;

/**
 * Class ValidateAudienceCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Validator
 */
class ValidateAudienceCest
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Security\JWT\Validator :: validateAudience()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTValidatorValidateAudience(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Validator - validateAudience()');

        $token = $this->newToken();
        $I->expectThrowable(
            new ValidatorException(
                "Validation: audience not allowed"
            ),
            function () use ($token, $I) {
                $validator = new Validator($token);
                $I->assertInstanceOf(Validator::class, $validator);
                $validator->validateAudience("unknown");
            }
        );
    }
}
