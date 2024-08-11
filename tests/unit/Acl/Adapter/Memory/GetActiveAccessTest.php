<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Acl\Adapter\Memory;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Enum;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetActiveAccessTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveAccess() - default
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryGetActiveAccess(): void
    {
        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);
        $acl->addRole('Guests');
        $acl->addComponent('Login', ['help', 'index']);
        $acl->allow('Guests', 'Login', '*');

        $actual = $acl->isAllowed('Guests', 'Login', 'index');
        $this->assertTrue($actual);

        $this->assertSame('index', $acl->getActiveAccess());
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveAccess() - default
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryGetActiveAccessDefault(): void
    {
        $acl = new Memory();

        $this->assertNull($acl->getActiveAccess());
    }
}
