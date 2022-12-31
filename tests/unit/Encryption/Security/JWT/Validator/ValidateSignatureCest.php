<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Validator;

use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use Phalcon\Encryption\Security\JWT\Validator;
use Phalcon\Tests\Fixtures\Traits\JWTTrait;
use UnitTester;

/**
 * Class ValidateSignatureCest
 *
 * @package Phalcon\Tests\Unit\Encryption\Security\JWT\Validator
 */
class ValidateSignatureCest
{
    use JWTTrait;

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator ::
     * validateSignature()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityJWTValidatorValidateNotBefore(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security\JWT\Validator - validateSignature()');

        $signer     = new Hmac();
        $builder    = new Builder($signer);
        $expiry     = strtotime('+1 day');
        $issued     = strtotime('now');
        $notBefore  = strtotime('-1 day');
        $passphrase = '&vsJBETaizP3A3VX&TPMJUqi48fJEgN7';

        $token = $builder
            ->setAudience('my-audience')
            ->setExpirationTime($expiry)
            ->setIssuer('Phalcon JWT')
            ->setIssuedAt($issued)
            ->setId('PH-JWT')
            ->setNotBefore($notBefore)
            ->setSubject('Mary had a little lamb')
            ->setPassphrase($passphrase)
            ->getToken()
        ;

        $validator = new Validator($token);
        $I->assertInstanceOf(Validator::class, $validator);

        $I->assertInstanceOf(
            Validator::class,
            $validator->validateSignature($signer, $passphrase)
        );
    }

    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Validator ::
     * validateSignature() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityJWTValidatorValidateNotBeforeException(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security\JWT\Validator - validateSignature()');

        $token      = $this->newToken();
        $signer     = new Hmac();
        $passphrase = '123456';
        $validator  = new Validator($token);
        $I->assertInstanceOf(Validator::class, $validator);

        $I->assertInstanceOf(
            Validator::class,
            $validator->validateSignature($signer, $passphrase)
        );

        $expected = ["Validation: the signature does not match"];
        $actual   = $validator->getErrors();
        $I->assertSame($expected, $actual);
    }
}
