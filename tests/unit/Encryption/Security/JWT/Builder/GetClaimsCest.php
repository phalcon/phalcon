<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Encryption\Security\JWT\Builder;

use Phalcon\Encryption\Security\JWT\Builder;
use Phalcon\Encryption\Security\JWT\Signer\Hmac;
use UnitTester;

/**
 * Class GetClaimsCest
 *
 * @package Phalcon\Tests\Unit\Encryption\Security\JWT\Builder
 */
class GetClaimsCest
{
    /**
     * Unit Tests Phalcon\Encryption\Security\JWT\Builder :: getClaims()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function encryptionSecurityJWTBuilderGetClaims(UnitTester $I)
    {
        $I->wantToTest('Encryption\Security\JWT\Builder - getClaims()');

        $signer  = new Hmac();
        $builder = new Builder($signer);

        $I->assertEmpty($builder->getClaims());
    }
}
