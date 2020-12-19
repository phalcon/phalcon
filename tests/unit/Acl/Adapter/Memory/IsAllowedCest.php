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

use Exception;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Component;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Exception as AclException;
use Phalcon\Acl\Role;
use Phalcon\Tests\Fixtures\Acl\TestComponentAware;
use Phalcon\Tests\Fixtures\Acl\TestRoleAware;
use Phalcon\Tests\Fixtures\Acl\TestRoleComponentAware;
use stdClass;
use UnitTester;

/**
 * Class IsAllowedCest
 *
 * @package Phalcon\Tests\Unit\Acl\Adapter\Memory
 */
class IsAllowedCest
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - default
     *
     * @issue  12573
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedDefault(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - default');

        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);

        $acl->addComponent(
            new Component('Post'),
            [
                'index',
                'update',
                'create',
            ]
        );

        $acl->addRole(
            new Role('Guests')
        );

        $acl->allow('Guests', 'Post', 'index');

        $I->assertTrue(
            $acl->isAllowed('Guests', 'Post', 'index')
        );

        $I->assertFalse(
            $acl->isAllowed('Guests', 'Post', 'update')
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - objects
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedObjects(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - objects');

        $acl = new Memory();

        $acl->setDefaultAction(
            Enum::DENY
        );

        $role = new Role('Guests');

        $component = new Component('Post');

        $acl->addRole($role);

        $acl->addComponent(
            $component,
            [
                'index',
                'update',
                'create',
            ]
        );

        $acl->allow('Guests', 'Post', 'index');

        $I->assertTrue(
            $acl->isAllowed($role, $component, 'index')
        );

        $I->assertFalse(
            $acl->isAllowed($role, $component, 'update')
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - same class
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedSameClass(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - same class');

        $acl = new Memory();

        $acl->setDefaultAction(
            Enum::DENY
        );

        $role      = new TestRoleComponentAware(1, 'User', 'Admin');
        $component = new TestRoleComponentAware(2, 'User', 'Admin');

        $acl->addRole('Admin');

        $acl->addComponent(
            'User',
            ['update']
        );

        $acl->allow(
            'Admin',
            'User',
            ['update'],
            function (TestRoleComponentAware $admin, TestRoleComponentAware $user) {
                return $admin->getUser() == $user->getUser();
            }
        );

        $I->assertFalse(
            $acl->isAllowed($role, $component, 'update')
        );

        $I->assertTrue(
            $acl->isAllowed($role, $role, 'update')
        );

        $I->assertTrue(
            $acl->isAllowed($component, $component, 'update')
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - function no parameters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedFunctionNoParameters(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - no parameters');

        $acl = new Memory();

        $acl->setDefaultAction(
            Enum::DENY
        );

        $acl->addRole('Admin');
        $acl->addComponent('User', ['update']);
        $acl->allow(
            'Admin',
            'User',
            ['update'],
            function () {
                return true;
            }
        );

        $I->assertTrue(
            $acl->isAllowed('Admin', 'User', 'update')
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - function more
     * parameters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedFunctionMoreParameters(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - more parameters');

        $I->expectThrowable(
            new Exception(
                'Number of parameters in array is higher than the ' .
                'number of parameters in defined function when checking if ' .
                '"Members" can "update" "Post". Extra parameters will be ignored.',
                512
            ),
            function () use ($I) {
                $acl = new Memory();

                $acl->setDefaultAction(Enum::ALLOW);
                $acl->setNoArgumentsDefaultAction(Enum::DENY);

                $acl->addRole('Members');
                $acl->addComponent('Post', ['update']);

                $member = new TestRoleAware(2, 'Members');
                $model  = new TestComponentAware(2, 'Post');

                $acl->allow(
                    'Members',
                    'Post',
                    'update',
                    function ($parameter) {
                        return $parameter % 2 == 0;
                    }
                );

                $acl->isAllowed(
                    $member,
                    $model,
                    'update',
                    [
                        'parameter' => 1,
                        'one'       => 2,
                    ]
                );
            }
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - function not enough
     * parameters
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedFunctionNotEnoughParameters(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - more parameters');

        $I->expectThrowable(
            new AclException(
                'You did not provide all necessary parameters for the ' .
                'defined function when checking if "Members" can "update" for "Post".'
            ),
            function () use ($I) {
                $acl = new Memory();

                $acl->setDefaultAction(Enum::ALLOW);
                $acl->setNoArgumentsDefaultAction(Enum::DENY);

                $acl->addRole('Members');
                $acl->addComponent('Post', ['update']);

                $member = new TestRoleAware(2, 'Members');
                $model  = new TestComponentAware(2, 'Post');

                $acl->allow(
                    'Members',
                    'Post',
                    'update',
                    function ($parameter, $value) {
                        return $parameter % $value == 0;
                    }
                );

                $acl->isAllowed(
                    $member,
                    $model,
                    'update',
                    [
                        'parameter' => 1,
                        'one'       => 2,
                    ]
                );
            }
        );
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: isAllowed() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryIsAllowedException(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - isAllowed() - exception');

        $I->expectThrowable(
            new AclException(
                'Object passed as roleName must implement ' .
                'Phalcon\Acl\RoleAwareInterface or Phalcon\Acl\RoleInterface'
            ),
            function () {
                $acl = new Memory();
                $acl->setDefaultAction(Enum::DENY);
                $acl->addRole('Member');
                $acl->addComponent('Post', ['update']);
                $acl->allow('Member', 'Post', 'update');
                $acl->isAllowed(new stdClass(), 'Post', 'update');
            }
        );

        $I->expectThrowable(
            new AclException(
                'Object passed as componentName must implement ' .
                'Phalcon\Acl\ComponentAwareInterface or Phalcon\Acl\ComponentInterface'
            ),
            function () {
                $acl = new Memory();
                $acl->setDefaultAction(Enum::DENY);
                $acl->addRole('Member');
                $acl->addComponent('Post', ['update']);
                $acl->allow('Member', 'Post', 'update');
                $acl->isAllowed('Member', new stdClass(), 'update');
            }
        );
    }
}
