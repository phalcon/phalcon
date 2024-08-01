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
use UnitTester;

class InterfaceDefinitionCest extends AbstractDefinitionTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsInterfaceDefinitionConstructorNotInterface(UnitTester $I)
    {
        $I->expectThrowable(
            new NotFound(
                "Interface '" . TestWithInterface::class . "' not found."
            ),
            function () {
                new InterfaceDefinition(TestWithInterface::class);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsInterfaceDefinitionClass(UnitTester $I)
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $definition->class(stdClass::class);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsInterfaceDefinitionFactory(UnitTester $I)
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $definition->factory(
            function (Container $container) {
                return new stdClass();
            }
        );

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsInterfaceDefinitionClassNotFound(UnitTester $I)
    {
        $I->expectThrowable(
            new NotFound("Class 'NoSuchClass' not found."),
            function () {
                $definition = new InterfaceDefinition(TestInterface::class);
                $definition->class('NoSuchClass');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsInterfaceDefinitionClassNotDefined(UnitTester $I)
    {
        $definition = new InterfaceDefinition(TestInterface::class);
        $this->assertNotInstantiable(
            $I,
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
}
