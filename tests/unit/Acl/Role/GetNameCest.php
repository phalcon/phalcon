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

class GetNameCest
{
    /**
     * Tests Phalcon\Acl\Role :: getName()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclRoleGetName(UnitTester $I)
    {
        $I->wantToTest('Acl\Role - getName()');

        $role = new Role('Administrators');

        $I->assertEquals('Administrators', $role->getName());
    }
}
