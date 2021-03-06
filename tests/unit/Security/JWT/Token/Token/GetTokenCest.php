<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Security\JWT\Token\Token;

use Phalcon\Security\JWT\Token\Item;
use Phalcon\Security\JWT\Token\Signature;
use Phalcon\Security\JWT\Token\Token;
use UnitTester;

/**
 * Class GetTokenCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Token\Token
 */
class GetTokenCest
{
    /**
     * Unit Tests Phalcon\Security\JWT\Token\Token :: getToken()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTTokenTokenGetToken(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Token\Token - getToken()');

        $headers   = new Item(["typ" => "JWT"], "header-encoded");
        $claims    = new Item(["aud" => ["valid-audience"]], "claim-encoded");
        $signature = new Signature("signature-hash", "signature-encoded");

        $token = new Token($headers, $claims, $signature);

        $I->assertEquals(
            "header-encoded.claim-encoded.signature-encoded",
            $token->getToken()
        );
    }
}
