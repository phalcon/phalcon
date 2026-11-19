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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Support\Di\SomeComponent;
use Phalcon\Tests\Support\Di\SomeServiceProvider;
use ReflectionMethod;

final class RegisterTest extends AbstractUnitTestCase
{
    /**
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

    /**
     * The parameter of ServiceProviderInterface::register() has the cphalcon
     * name.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/832
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testDiServiceProviderInterfaceRegisterParameterName(): void
    {
        $method = new ReflectionMethod(ServiceProviderInterface::class, 'register');

        $this->assertSame('di', $method->getParameters()[0]->getName());
    }
}
