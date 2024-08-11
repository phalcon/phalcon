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
use Phalcon\Tests\AbstractUnitTestCase;

final class ToStringTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Role :: __toString()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclRoleToString(): void
    {
        $role = new Role('Administrator');

        $expected = 'Administrator';
        $actual   = $role->__toString();
        $this->assertSame($expected, $actual);

        $expected = 'Administrator';
        $actual   = (string)$role;
        $this->assertSame($expected, $actual);
    }
}
