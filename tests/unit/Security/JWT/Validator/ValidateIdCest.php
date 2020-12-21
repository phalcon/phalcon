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
 * Class ValidateIdCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Validator
 */
class ValidateIdCest
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Security\JWT\Validator :: validateId()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTValidatorValidateId(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Validator - validateId()');

        $token = $this->newToken();
        $I->expectThrowable(
            new ValidatorException(
                "Validation: incorrect Id"
            ),
            function () use ($token, $I) {
                $validator = new Validator($token);
                $I->assertInstanceOf(Validator::class, $validator);
                $validator->validateId("unknown");
            }
        );
    }
}
