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
use Phalcon\Acl\Role;
use UnitTester;

/**
 * Class GetActiveKeyCest
 *
 * @package Phalcon\Tests\Unit\Acl\Adapter\Memory
 */
class GetActiveKeyCest
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveKey()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function aclAdapterMemoryGetActiveKey(UnitTester $I)
    {
        $I->wantToTest('Acl\Adapter\Memory - getActiveKey()');

        $acl = new Memory();
        $acl->addRole(new Role('Guests'));
        $acl->addComponent(
            new Component('Post'),
            ['index', 'update', 'create']
        );

        $acl->allow('Guests', 'Post', 'create');

        $I->assertTrue(
            $acl->isAllowed('Guests', 'Post', 'create')
        );

        $I->assertEquals('Guests!Post!create', $acl->getActiveKey());
    }
}
