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
 * Class GetSignatureCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Token\Token
 */
class GetSignatureCest
{
    /**
     * Unit Tests Phalcon\Security\JWT\Token\Token :: getSignature()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTTokenTokenGetSignature(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Token\Token - getSignature()');

        $headers   = new Item(["typ" => "JWT"], "header-encoded");
        $claims    = new Item(["aud" => ["valid-audience"]], "claim-encoded");
        $signature = new Signature("signature-hash", "signature-encoded");

        $token = new Token($headers, $claims, $signature);

        $I->assertInstanceOf(Signature::class, $token->getSignature());
        $I->assertEquals("signature-encoded", $token->getSignature()
                                                    ->getEncoded());
    }
}
