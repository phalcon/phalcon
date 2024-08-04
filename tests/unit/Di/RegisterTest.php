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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Tests\Fixtures\Di\SomeComponent;
use Phalcon\Tests\Fixtures\Di\SomeServiceProvider;
use Phalcon\Tests\UnitTestCase;

class RegisterTest extends UnitTestCase
{
    /**
     * Unit Tests Phalcon\Di :: register()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiRegister(): void
    {
        $container = new Di();
        $container->register(new SomeServiceProvider());

        $expected = 'bar';
        $actual   = $container->get('foo');
        $this->assertSame($expected, $actual);

        $expected = SomeComponent::class;
        $actual   = $container->get('fooAction');
        $this->assertInstanceOf($expected, $actual);
    }
}
