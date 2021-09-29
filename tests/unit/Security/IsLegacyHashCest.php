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

namespace Phalcon\Tests\Unit\Security;

use Phalcon\Security\Security;
use UnitTester;

/**
 * Class IsLegacyHashCest
 *
 * @package Phalcon\Tests\Unit\Security
 */
class IsLegacyHashCest
{
    /**
     * Tests Phalcon\Security :: isLegacyHash()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function securityIsLegacyHash(UnitTester $I)
    {
        $I->wantToTest('Security - isLegacyHash()');

        $oldHash  = '$2a$10$JnD9Za73U2dIIjd.Uvn1IuNVQhXNQpHIu13WzlL70q.WhfKT9Yuc2';
        $security = new Security();

        $I->assertTrue($security->isLegacyHash($oldHash));
        $I->assertFalse($security->isLegacyHash('Phalcon'));
    }
}
