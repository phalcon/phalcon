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
use Phalcon\Security\JWT\Signer\Hmac;
use UnitTester;

/**
 * Class GetClaimsCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Builder
 */
class GetClaimsCest
{
    /**
     * Unit Tests Phalcon\Security\JWT\Builder :: getClaims()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTBuilderGetClaims(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Builder - getClaims()');

        $signer  = new Hmac();
        $builder = new Builder($signer);

        $I->assertEmpty($builder->getClaims());
    }
}
