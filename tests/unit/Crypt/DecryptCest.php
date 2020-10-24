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

namespace Phalcon\Tests\Unit\Crypt;

use Phalcon\Crypt\Crypt;
use Phalcon\Crypt\Exception;
use Phalcon\Crypt\Mismatch;
use UnitTester;

/**
 * Class DecryptCest
 *
 * @package Phalcon\Tests\Unit\Crypt
 */
class DecryptCest
{
    /**
     * Tests Phalcon\Crypt\Crypt :: decrypt() - no exception on key mismatch
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13379
     */
    public function cryptDecryptNoExceptionOnKeyMismatch(UnitTester $I)
    {
        $I->wantToTest(
            'Crypt - decrypt() not throwing Exception on key mismatch'
        );

        $crypt = new Crypt();

        $actual = $crypt->decrypt(
            $crypt->encrypt('le text', 'encrypt key'),
            'wrong key'
        );

        $I->assertNotEmpty($actual);
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: decrypt() - exception hash mismatch
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13379
     */
    public function cryptDecryptExceptionHashMismatch(UnitTester $I)
    {
        $I->wantToTest('Crypt - decrypt() - exception hash mismatch');

        $I->expectThrowable(
            new Mismatch('Hash does not match.'),
            function () {
                $crypt = new Crypt();

                $crypt->useSigning(true);

                $crypt->decrypt(
                    $crypt->encrypt('le text', 'encrypt key'),
                    'wrong key'
                );
            }
        );
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: decrypt() - signed key
     * Tests decrypt using HMAC
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13379
     */
    public function cryptDecryptSignedString(UnitTester $I)
    {
        $I->wantToTest('Crypt - decrypt() - signed key');
        $crypt = new Crypt();

        $crypt->useSigning(true);
        $crypt->setKey('secret');

        $expected  = 'le text';
        $encrypted = $crypt->encrypt($expected);
        $actual    = $crypt->decrypt($encrypted);

        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: decrypt() - empty key
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptEncryptExceptionEmptyKey(UnitTester $I)
    {
        $I->wantToTest('Crypt - decrypt() - exception empty key');

        $I->expectThrowable(
            new Exception(
                'Decryption key cannot be empty'
            ),
            function () {
                $crypt = new Crypt();
                $crypt->decrypt('sample text', '');
            }
        );
    }
}
