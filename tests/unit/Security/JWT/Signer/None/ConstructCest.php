<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Security\JWT\Signer\None;

use Phalcon\Security\JWT\Signer\None;
use UnitTester;

/**
 * Class ConstructCest
 *
 * @package Phalcon\Tests\Unit\Security\JWT\Signer\None
 */
class ConstructCest
{
    /**
     * Unit Tests Phalcon\Security\JWT\Signer\None :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpJWTSignerNoneConstruct(UnitTester $I)
    {
        $I->wantToTest('Http\JWT\Signer\None - __construct()');

        $signer = new None();
        $I->assertInstanceOf(None::class, $signer);
    }
}
