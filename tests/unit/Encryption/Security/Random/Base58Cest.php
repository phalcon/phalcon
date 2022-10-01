<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\Random;

use Phalcon\Encryption\Security\Random;
use UnitTester;

/**
 * Class Base58Cest
 *
 * @package Phalcon\Tests\Unit\Encryption\Security\Random
 */
class Base58Cest
{
    /**
     * Tests Phalcon\Encryption\Security\Random :: base58()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityRandomBase58(UnitTester $I)
    {
        $I->wantToTest("Encryption\Security\Random - base58()");

        $random = new Random();


        $base58 = $random->base58();

        // Test forbidden characters
        $I->assertRegExp('/^[1-9A-Za-z][^OIl0]+$/', $base58);

        // Default length is 16 bytes
        $I->assertSame(16, strlen($base58));


        $differentString = $random->base58();
        // Buy lottery ticket if this fails (or fix the bug)
        $I->assertNotEquals($base58, $differentString);


        $expectedLength = 30;
        $base58         = $random->base58($expectedLength);
        $I->assertSame($expectedLength, strlen($base58));
    }
}
