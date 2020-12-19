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
 * Class GetSetDefaultActionCest
 *
 * @package Phalcon\Tests\Unit\Acl\Adapter\Memory
 */
class GetSetDefaultActionCest
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getDefaultAction()/setDefaultAction()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryGetSetDefaultAction(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - getDefaultAction()/setDefaultAction()');

        $acl = new Memory();
        $acl->setDefaultAction(Enum::ALLOW);

        $I->assertEquals(Enum::ALLOW, $acl->getDefaultAction());
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getDefaultAction()/setDefaultAction()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryGetSetDefaultActionDefault(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - getDefaultAction()/setDefaultAction() - default');

        $acl = new Memory();

        $I->assertEquals(Enum::DENY, $acl->getDefaultAction());
    }
}
