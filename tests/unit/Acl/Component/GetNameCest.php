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

namespace Phalcon\Tests\Unit\Acl\Component;

use Phalcon\Acl\Component;
use UnitTester;

/**
 * Class GetNameCest
 *
 * @package Phalcon\Tests\Unit\Acl\Component
 */
class GetNameCest
{
    /**
     * Tests Phalcon\Acl\Component :: getName()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function aclComponentGetName(UnitTester $I)
    {
        $I->wantToTest('Acl\Component - getName()');

        $component = new Component('Customers');

        $I->assertSame('Customers', $component->getName());
    }
}
