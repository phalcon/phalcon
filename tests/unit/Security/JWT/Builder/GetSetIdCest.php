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
 * Class GetSetIdCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Builder
 */
class GetSetIdCest
{
    /**
     * Unit Tests Phalcon\Security\JWT\Builder :: getId()/setId()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTBuilderGetSetId(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Builder - getId()/setId()');

        $signer  = new Hmac();
        $builder = new Builder($signer);

        $I->assertNull($builder->getId());

        $return = $builder->setId('id');
        $I->assertInstanceOf(Builder::class, $return);

        $I->assertEquals('id', $builder->getId());
    }
}
