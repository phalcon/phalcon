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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetActiveComponentTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveComponent()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryGetActiveComponent(): void
    {
        $acl = new Memory();
        $acl->setDefaultAction(Enum::DENY);
        $acl->addRole('Guests');
        $acl->addComponent(
            'Login',
            ['help', 'index']
        );

        $acl->allow('Guests', 'Login', '*');

        $actual = $acl->isAllowed('Guests', 'Login', 'index');
        $this->assertTrue($actual);

        $expected = 'Login';
        $actual   = $acl->getActiveComponent();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getActiveComponent() - default
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryGetActiveComponentDefault(): void
    {
        $acl = new Memory();

        $this->assertNull($acl->getActiveComponent());
    }
}
