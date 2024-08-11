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
use Phalcon\Acl\Component;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Role;
use Phalcon\Tests\AbstractUnitTestCase;

final class AddInheritTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addInherit()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryAddInherit(): void
    {
        $acl = new Memory();

        $acl->addRole(new Role('administrator'));
        $acl->addRole(new Role('apprentice'));

        $addedInherit = $acl->addInherit('administrator', 'apprentice');

        $this->assertTrue($addedInherit);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addInherit() - infinite loop
     * exception
     *
     * @return void
     *
     * @author  Phalcon Team <team@phalcon.io>
     * @since   2021-09-27
     */
    public function testAclAdapterMemoryAddInheritInfiniteLoopException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Role 'administrator' (to inherit) produces an infinite loop"
        );

        $acl = new Memory();

        $acl->addRole(new Role('administrator'));
        $acl->addRole(new Role('member'));
        $acl->addRole(new Role('guest'));

        $acl->addInherit('administrator', 'member');
        // Twice to ensure it gets ignored
        $acl->addInherit('administrator', 'member');
        $acl->addInherit('member', 'guest');
        $acl->addInherit('guest', 'administrator');
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addInherit()
     *
     * @return void
     *
     * @author <jenovateurs>
     * @since  2019-12-05
     */
    public function testAclAdapterMemoryAddInheritIsAllow(): void
    {
        $acl = new Memory();

        //New role
        $acl->addRole(new Role('administrator'));
        $acl->addRole(new Role('apprentice'));

        //New Component
        $acl->addComponent(new Component('folder'), 'list');
        $acl->addComponent(new Component('folder'), 'add');

        //Add Inherit
        $actual = $acl->addInherit('administrator', 'apprentice');
        $this->assertTrue($actual);

        // Add Inherit twice to ensure that we have no duplicates
        $actual = $acl->addInherit('administrator', 'apprentice');
        $this->assertTrue($actual);

        //Allow access
        $acl->allow('apprentice', 'folder', 'list');
        $acl->allow('administrator', 'folder', 'add');

        //Check access
        $actual = $acl->isAllowed('apprentice', 'folder', 'list');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('administrator', 'folder', 'add');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('apprentice', 'folder', 'add');
        $this->assertFalse($actual);

        $actual = $acl->isAllowed('administrator', 'folder', 'list');
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addInherit() - same name
     *
     * @return void
     *
     * @author  Phalcon Team <team@phalcon.io>
     * @since   2021-09-27
     */
    public function testAclAdapterMemoryAddInheritSameName(): void
    {
        $acl = new Memory();

        // New role
        $acl->addRole(new Role('administrator'));
        $acl->addRole(new Role('apprentice'));

        // New Component
        $acl->addComponent(new Component('folder'), 'list');
        $acl->addComponent(new Component('folder'), 'add');

        // Add Inherit
        $actual = $acl->addInherit('administrator', 'administrator');
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addInherit() - unknown role exception
     *
     * @return void
     *
     * @author  Phalcon Team <team@phalcon.io>
     * @since   2021-09-27
     */
    public function testAclAdapterMemoryAddInheritUnknownRole(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Role 'unknown' (to inherit) does not exist in the role list"
        );

        $acl = new Memory();
        $acl->addRole(new Role('administrator'));

        //Add Inherit
        $acl->addInherit('administrator', 'unknown');
    }
}
