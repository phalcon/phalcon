<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Acl;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Adapter\Storage;
use Phalcon\Acl\Exceptions\CircularInheritanceError;
use Phalcon\Acl\Exceptions\RoleNotFoundException;
use Phalcon\Storage\Adapter\Memory as StorageMemory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function array_keys;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/886
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testAclAdapterMemoryAddInheritIntRole(): void
    {
        $acl = new Memory();
        $acl->addRole('a');

        try {
            $acl->addInherit('a', 123);
            $this->fail('No exception');
        } catch (RoleNotFoundException $ex) {
            $this->assertSame(
                "Role '123' (to inherit) does not exist in the role list",
                $ex->getMessage()
            );
        }

        $acl->addRole('1');
        $acl->addInherit('1', 'a');

        $this->expectException(CircularInheritanceError::class);
        $this->expectExceptionMessage("Role '1' (to inherit) produces an infinite loop");

        $acl->addInherit('a', 1);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testAclAdapterMemoryIntNames(): void
    {
        $acl = new Memory();
        $acl->addComponent(123, 'read');
        $this->assertEquals([123], array_keys($acl->getComponents()));

        $this->assertTrue($acl->addComponent('c', [123]));
        $acl->dropComponentAccess('c', [123]);

        $acl->addRole('1');
        $acl->addRole('r');
        $acl->addComponent('2', 'read');
        $acl->addComponent('d', ['1']);
        $acl->allow('1', '2', 'read');
        $acl->allow('r', '2', 'read');
        $acl->allow('r', 'd', [1]);

        $this->assertTrue($acl->isAllowed(1, '2', 'read'));
        $this->assertTrue($acl->isAllowed('r', 2, 'read'));
        $this->assertTrue($acl->isAllowed('r', 'd', '1'));

        $acl = new Memory();
        $acl->addRole('r');
        $acl->addComponent('d', ['1']);
        $acl->allow('r', 'd', 1);

        $this->assertTrue($acl->isAllowed('r', 'd', '1'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testAclAdapterStorageSnapshotIntDescription(): void
    {
        $store = new StorageMemory(new SerializerFactory());
        $store->set(
            'acl-data',
            [
                'version'         => 1,
                'access'          => [],
                'accessList'      => [],
                'components'      => ['c' => 123],
                'componentsNames' => ['c' => true],
                'roles'           => ['r' => 123],
                'roleInherits'    => [],
            ]
        );

        $acl = new Storage($store, 'acl-data');

        $this->assertSame('123', $acl->getRoles()['r']->getDescription());
        $this->assertSame('123', $acl->getComponents()['c']->getDescription());
    }
}
