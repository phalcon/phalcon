<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Security\JWT\Builder;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Exceptions\ValidatorException;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Token;
use Phalcon\Tests\Fixtures\Traits\JWTTrait;
use UnitTester;

/**
 * Class GetTokenCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Builder
 */
class GetTokenCest
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Security\JWT\Builder :: getToken()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTBuilderGetToken(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Builder - getToken()');

        $token = $this->newToken();

        $I->assertInstanceOf(Token::class, $token);

        $parts = explode('.', $token->getToken());
        $I->assertCount(3, $parts);
    }

    /**
     * Unit Tests Phalcon\Security\JWT\Builder :: getToken() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTBuilderGetTokenException(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Builder - getToken() - exception');

        $I->expectThrowable(
            new ValidatorException(
                'Invalid passphrase (empty)'
            ),
            function () {
                $signer  = new Hmac();
                $builder = new Builder($signer);
                $token   = $builder->getToken();
            }
        );
    }
}
