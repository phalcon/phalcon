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

final class ToStringTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Component :: __toString()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclComponentToString(): void
    {
        $component = new Component('Customers');

        $expected = 'Customers';
        $actual   = $component->__toString();
        $this->assertSame($expected, $actual);

        $expected = 'Customers';
        $actual   = (string)$component;
        $this->assertSame($expected, $actual);
    }
}
