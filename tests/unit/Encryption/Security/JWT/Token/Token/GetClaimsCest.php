<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Token\Token;

use Phalcon\Encryption\Security\JWT\Token\Item;
use Phalcon\Encryption\Security\JWT\Token\Signature;
use Phalcon\Encryption\Security\JWT\Token\Token;
use UnitTester;

/**
 * Class GetClaimsCest
 *
 * @package Phalcon\Tests\Unit\Encryption\Security\JWT\Token\Token
 */
class GetClaimsCest
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Token\Token :: getClaims()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityJWTTokenTokenGetClaims(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security\JWT\Token\Token - getClaims()');

        $headers   = new Item(["typ" => "JWT"], "header-encoded");
        $claims    = new Item(["sub" => "valid-subject"], "claim-encoded");
        $signature = new Signature("signature-hash", "signature-encoded");

        $token = new Token($headers, $claims, $signature);

        $expected = Item::class;
        $actual   = $token->getClaims();
        $I->assertInstanceOf($expected, $actual);

        $expected = "valid-subject";
        $actual   = $token->getClaims()
                          ->get('sub')
        ;
        $I->assertSame($expected, $actual);
    }
}
