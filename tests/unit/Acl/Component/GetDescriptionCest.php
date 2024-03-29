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
 * Class GetDescriptionCest
 *
 * @package Phalcon\Tests\Unit\Acl\Component
 */
class GetDescriptionCest
{
    /**
     * Tests Phalcon\Acl\Component :: getDescription()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function aclComponentGetDescription(UnitTester $I)
    {
        $I->wantToTest('Acl\Component - getDescription()');

        $component = new Component('Customers', 'Customer management');

        $I->assertSame(
            'Customer management',
            $component->getDescription()
        );
    }

    /**
     * Tests Phalcon\Acl\Component :: getDescription() - empty
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function aclComponentGetDescriptionEmpty(UnitTester $I)
    {
        $I->wantToTest("Acl\Component - getDescription() - empty");

        $component = new Component('Customers');

        $I->assertEmpty($component->getDescription());
    }
}
