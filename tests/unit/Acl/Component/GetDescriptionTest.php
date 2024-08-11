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

namespace Phalcon\Tests\Unit\Acl\Component;

use Phalcon\Acl\Component;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetDescriptionTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Component :: getDescription()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclComponentGetDescription(): void
    {
        $component = new Component('Customers', 'Customer management');

        $expected = 'Customer management';
        $actual   = $component->getDescription();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Acl\Component :: getDescription() - empty
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclComponentGetDescriptionEmpty(): void
    {
        $component = new Component('Customers');

        $this->assertEmpty($component->getDescription());
    }
}
