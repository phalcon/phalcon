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

namespace Phalcon\Tests\Unit\Acl\Adapter\Memory;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use UnitTester;

/**
 * Class GetActiveComponentCest
 *
 * @package Phalcon\Tests\Unit\Acl\Adapter\Memory
 */
class GetActiveComponentCest
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveComponent() - default
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function aclAdapterMemoryGetActiveComponentDefault(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - getActiveComponent() - default');

        $acl = new Memory();

        $I->assertNull($acl->getActiveComponent());
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveComponent()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function aclAdapterMemoryGetActiveComponent(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - getActiveComponent()');

        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);
        $acl->addRole('Guests');
        $acl->addComponent(
            'Login',
            ['help', 'index']
        );

        $acl->allow('Guests', 'Login', '*');

        $actual = $acl->isAllowed('Guests', 'Login', 'index');
        $I->assertTrue($actual);

        $expected = 'Login';
        $actual   = $acl->getActiveComponent();
        $I->assertSame($expected, $actual);
    }
}
