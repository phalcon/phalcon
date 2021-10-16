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
use Phalcon\Crypt\Exception\Exception;
use UnitTester;

use function substr;

/**
 * Class EncryptCest
 *
 * @package Phalcon\Tests\Unit\Crypt
 */
class EncryptCest
{
    /**
     * Tests Phalcon\Crypt\Crypt :: encrypt()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptEncrypt(UnitTester $I)
    {
        $I->wantToTest('Crypt - encrypt()');

        $tests = [
            md5(uniqid())            => str_repeat('x', mt_rand(1, 255)),
            time() . time()          => str_shuffle('abcdefeghijklmnopqrst'),
            'le$ki12432543543543543' => '',
        ];

        $ciphers = [
            'AES-128-CBC',
            'AES-128-CFB',
            'AES-128-OFB',
            'AES128',
        ];

        $crypt = new Crypt();

        foreach ($ciphers as $cipher) {
            $crypt->setCipher($cipher);

            foreach ($tests as $key => $test) {
                $crypt->setKey(
                    substr($key, 0, 16)
                );

                $encryption = $crypt->encrypt($test);

                $actual = rtrim(
                    $crypt->decrypt($encryption),
                    "\0"
                );

                $I->assertEquals($test, $actual);
            }

            foreach ($tests as $key => $test) {
                $encryption = $crypt->encrypt(
                    $test,
                    substr($key, 0, 16)
                );

                $actual = rtrim(
                    $crypt->decrypt(
                        $encryption,
                        substr($key, 0, 16)
                    ),
                    "\0"
                );

                $I->assertEquals($test, $actual);
            }
        }
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: encrypt() - empty key
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptEncryptExceptionEmptyKey(UnitTester $I)
    {
        $I->wantToTest('Crypt - encrypt() - exception empty key');

        $I->expectThrowable(
            new Exception(
                'Encryption key cannot be empty'
            ),
            function () {
                $crypt = new Crypt();
                $crypt->encrypt('sample text', '');
            }
        );
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: encrypt() - unsupported algo
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptEncryptException(UnitTester $I)
    {
        $I->wantToTest('Crypt - encrypt() - exception');

        $I->expectThrowable(
            new Exception(
                "The cipher algorithm 'AES-128-ECB' is not supported on this system."
            ),
            function () {
                $crypt = new Crypt();

                $crypt->setCipher('AES-128-ECB');
            }
        );
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: encrypt() - gcm
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptEncryptGcm(UnitTester $I)
    {
        $I->wantToTest('Crypt - encrypt()');

        $ciphers = [
            'aes-128-gcm',
            'aes-128-ccm',
        ];

        $crypt = new Crypt();

        foreach ($ciphers as $cipher) {
            $crypt
                ->setCipher($cipher)
                ->setAuthTag('1234')
                ->setAuthData('abcd')
                ->setKey('123456')
            ;

            $encryption = $crypt->encrypt('phalcon');
            $actual     = $crypt->decrypt($encryption);
            $I->assertEquals('phalcon', $actual);
        }
    }
}
