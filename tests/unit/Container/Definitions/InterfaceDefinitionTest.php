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

namespace Phalcon\Tests\Unit\Container\Definitions;

use Phalcon\Container\Container;
use Phalcon\Container\Definitions\InterfaceDefinition;
use Phalcon\Container\Exception\NotDefined;
use Phalcon\Container\Exception\NotFound;
use Phalcon\Tests\Fixtures\Container\TestInterface;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use stdClass;

final class InterfaceDefinitionTest extends AbstractDefinitionBase
{
    /**
     * @return void
     */
    public function testContainerDefinitionsInterfaceDefinitionClass(): void
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $definition->class(stdClass::class);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsInterfaceDefinitionClassNotDefined(): void
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotDefined::class,
                    "Class/factory for interface definition '" .
                    TestInterface::class .
                    "' not set.",
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsInterfaceDefinitionClassNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage("Class 'NoSuchClass' not found.");

        $definition = new InterfaceDefinition(TestInterface::class);
        $definition->class('NoSuchClass');
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsInterfaceDefinitionConstructorNotInterface(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage(
            "Interface '" . TestWithInterface::class . "' not found."
        );

        (new InterfaceDefinition(TestWithInterface::class));
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsInterfaceDefinitionFactory(): void
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $definition->factory(
            function (Container $container) {
                return new stdClass();
            }
        );

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }
}
