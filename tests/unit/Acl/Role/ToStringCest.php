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

namespace Phalcon\Tests\Unit\Acl\Role;

use Phalcon\Acl\Role;
use UnitTester;

class ToStringCest
{
    /**
     * Tests Phalcon\Acl\Role :: __toString()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclRoleToString(UnitTester $I)
    {
        $I->wantToTest('Acl\Role - __toString()');

        $role = new Role('Administrator');

        $I->assertEquals('Administrator', $role->__toString());
        $I->assertEquals('Administrator', (string) $role);
    }
}
