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
use Phalcon\Acl\Enum;
use Phalcon\Acl\Role;
use Phalcon\Tests\UnitTestCase;

use function cacheDir;
use function file_get_contents;
use function file_put_contents;
use function serialize;
use function unserialize;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryConstruct(): void
    {
        $acl = new Memory();

        $this->assertInstanceOf(Memory::class, $acl);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: __construct() - constants
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryConstructConstants(): void
    {
        $this->assertSame(1, Enum::ALLOW);
        $this->assertSame(0, Enum::DENY);
    }

    /**
     * Tests negation of inherited Roles
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/65
     *
     * @return void
     *
     * @author  Phalcon Team <team@phalcon.io>
     * @since   2014-10-04
     */
    public function testAclNegationOfInheritedRoles(): void
    {
        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);

        $acl->addRole('Guests');
        $acl->addRole('Members', 'Guests');

        $acl->addComponent('Login', ['help', 'index']);

        $acl->allow('Guests', 'Login', '*');
        $acl->deny('Guests', 'Login', ['help']);
        $acl->deny('Members', 'Login', ['index']);

        $actual = $acl->isAllowed('Members', 'Login', 'index');
        $this->assertFalse($actual);

        $actual = $acl->isAllowed('Guests', 'Login', 'index');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Guests', 'Login', 'help');
        $this->assertFalse($actual);
    }

    /**
     * Tests negation of multilayer inherited Roles
     *
     * @return void
     *
     * @author  cq-z <64899484@qq.com>
     * @since   2018-10-10
     */
    public function testAclNegationOfMultilayerInheritedRoles(): void
    {
        $acl = new Memory();

        $acl->setDefaultAction(Enum::DENY);
        $acl->addRole('Guests1');
        $acl->addRole('Guests12', 'Guests1');
        $acl->addRole('Guests2');
        $acl->addRole('Guests22', 'Guests2');
        $acl->addRole('Members', ['Guests12', 'Guests22']);

        $acl->addComponent('Login', ['help', 'index']);
        $acl->addComponent('Logout', ['help', 'index']);

        $acl->allow('Guests1', 'Login', '*');
        $acl->deny('Guests12', 'Login', ['help']);

        $acl->deny('Guests2', 'Logout', '*');
        $acl->allow('Guests22', 'Logout', ['index']);

        $actual = $acl->isAllowed('Members', 'Login', 'index');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Members', 'Login', 'help');
        $this->assertFalse($actual);

        $actual = $acl->isAllowed('Members', 'Logout', 'help');
        $this->assertFalse($actual);

        $actual = $acl->isAllowed('Members', 'Login', 'index');
        $this->assertTrue($actual);
    }

    /**
     * Tests negation of multiple inherited Roles
     *
     * @return void
     *
     * @author  cq-z <64899484@qq.com>
     * @since   2018-10-10
     */
    public function testAclNegationOfMultipleInheritedRoles(): void
    {
        $acl = new Memory();

        $acl->setDefaultAction(Enum::DENY);
        $acl->addRole('Guests');
        $acl->addRole('Guests2');

        $acl->addRole(
            'Members',
            [
                'Guests',
                'Guests2',
            ]
        );

        $acl->addComponent(
            'Login',
            [
                'help',
                'index',
            ]
        );

        $acl->allow('Guests', 'Login', '*');
        $acl->deny('Guests2', 'Login', ['help']);
        $acl->deny('Members', 'Login', ['index']);

        $actual = $acl->isAllowed('Members', 'Login', 'index');
        $this->assertFalse($actual);

        $actual = $acl->isAllowed('Guests', 'Login', 'help');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Members', 'Login', 'help');
        $this->assertTrue($actual);
    }

    /**
     * Tests serializing the ACL
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-04
     */
    public function testAclSerialize(): void
    {
        /** @var string $filename */
        $filename = $this->getNewFileName('acl', 'log');

        $acl          = new Memory();
        $aclRole      = new Role('Administrators', 'Super User access');
        $aclComponent = new Component('Customers', 'Customer management');

        $acl->addRole($aclRole);
        $acl->addComponent($aclComponent, ['search', 'destroy']);

        $acl->allow('Administrators', 'Customers', 'search');
        $acl->deny('Administrators', 'Customers', 'destroy');

        file_put_contents(cacheDir($filename), serialize($acl));

        $acl      = null;
        $contents = file_get_contents(cacheDir($filename));
        $this->safeDeleteFile(cacheDir($filename));

        $acl = unserialize($contents);

        $this->assertInstanceOf(Memory::class, $acl);
        $actual = $acl->isRole('Administrators');
        $this->assertTrue($actual);
        $actual = $acl->isComponent('Customers');
        $this->assertTrue($actual);
        $actual = $acl->isAllowed('Administrators', 'Customers', 'search');
        $this->assertTrue($actual);
        $actual = $acl->isAllowed('Administrators', 'Customers', 'destroy');
        $this->assertFalse($actual);
    }

    /**
     * Tests function in Acl Allow Method
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/12004
     *
     * @return void
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2016-07-22
     */
    public function testIssue12004(): void
    {
        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);

        $roleGuest      = new Role('guest');
        $roleUser       = new Role('user');
        $roleAdmin      = new Role('admin');
        $roleSuperAdmin = new Role('superadmin');

        $acl->addRole($roleGuest);
        $acl->addRole($roleUser, $roleGuest);
        $acl->addRole($roleAdmin, $roleUser);
        $acl->addRole($roleSuperAdmin, $roleAdmin);

        $acl->addComponent('payment', ['paypal', 'facebook',]);

        $acl->allow($roleGuest->getName(), 'payment', 'paypal');
        $acl->allow($roleGuest->getName(), 'payment', 'facebook');
        $acl->allow($roleUser->getName(), 'payment', '*');

        $actual = $acl->isAllowed($roleUser->getName(), 'payment', 'notSet');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed($roleUser->getName(), 'payment', '*');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed($roleAdmin->getName(), 'payment', 'notSet');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed($roleAdmin->getName(), 'payment', '*');
        $this->assertTrue($actual);
    }

    /**
     * Tests acl with adding new rule for Role after adding wildcard rule
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/2648
     *
     * @return void
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2016-10-01
     */
    public function testWildCardLastRole(): void
    {
        $acl = new Memory();

        $acl->addRole(new Role('Guests'));
        $acl->addComponent(
            new Component('Post'),
            [
                'index',
                'update',
                'create',
            ]
        );

        $acl->allow('Guests', 'Post', 'create');
        $acl->allow('*', 'Post', 'index');
        $acl->allow('Guests', 'Post', 'update');

        $actual = $acl->isAllowed('Guests', 'Post', 'create');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Guests', 'Post', 'index');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Guests', 'Post', 'update');
        $this->assertTrue($actual);
    }

    /**
     * Tests adding wildcard rule second time
     *
     * @issue   https://github.com/phalcon/cphalcon/issues/2648
     *
     * @return void
     *
     * @author  Wojciech Slawski <jurigag@gmail.com>
     * @since   2016-10-01
     */
    public function testWildCardSecondTime(): void
    {
        $acl = new Memory();

        $acl->addRole(new Role('Guests'));
        $acl->addComponent(
            new Component('Post'),
            [
                'index',
                'update',
                'create',
            ]
        );

        $acl->allow('Guests', 'Post', 'create');
        $acl->allow('*', 'Post', 'index');
        $acl->allow('*', 'Post', 'update');

        $actual = $acl->isAllowed('Guests', 'Post', 'create');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Guests', 'Post', 'index');
        $this->assertTrue($actual);

        $actual = $acl->isAllowed('Guests', 'Post', 'update');
        $this->assertTrue($actual);
    }
}
