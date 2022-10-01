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

namespace Phalcon\Tests\Unit\Encryption\Security;

use Phalcon\Encryption\Security;
use UnitTester;

class ConstructCest
{
    /**
     * Tests Phalcon\Security :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityConstruct(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security - __construct()');

        $security = new Security();
        $I->assertInstanceOf(Security::class, $security);
    }

    /**
     * Tests the Security constants
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSecurityConstants(UnitTester $I)
    {
        $I->assertSame(0, Security::CRYPT_DEFAULT);
        $I->assertSame(3, Security::CRYPT_MD5);
        $I->assertSame(4, Security::CRYPT_BLOWFISH);
        $I->assertSame(5, Security::CRYPT_BLOWFISH_A);
        $I->assertSame(6, Security::CRYPT_BLOWFISH_X);
        $I->assertSame(7, Security::CRYPT_BLOWFISH_Y);
        $I->assertSame(8, Security::CRYPT_SHA256);
        $I->assertSame(9, Security::CRYPT_SHA512);
    }
}
