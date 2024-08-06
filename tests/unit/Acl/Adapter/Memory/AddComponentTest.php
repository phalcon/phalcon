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
use Phalcon\Tests\UnitTestCase;

final class AddComponentTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addComponent() - numeric key
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryAddComponentNumericKey(): void
    {
        $acl       = new Memory();
        $component = new Component('11', 'Customer component');
        $actual    = $acl->addComponent($component, ['index']);

        $this->assertTrue($actual);
        $this->assertTrue($acl->isComponent('11'));
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addComponent() - object
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryAddComponentObject(): void
    {
        $acl       = new Memory();
        $component = new Component('Customer', 'Customer component');
        $actual    = $acl->addComponent($component, ['index']);

        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Acl\Adapter\Memory :: addComponent() - string
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryAddComponentString(): void
    {
        $acl = new Memory();

        $actual = $acl->addComponent('Customer', ['index']);
        $this->assertTrue($actual);
    }
}
