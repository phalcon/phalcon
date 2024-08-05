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
use Phalcon\Container\Definitions\ClassDefinition;
use Phalcon\Container\Exception\NotAllowed;
use Phalcon\Container\Exception\NotDefined;
use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Exception\NotInstantiated;
use Phalcon\Container\Lazy\Call;
use Phalcon\Tests\Fixtures\Container\TestBadHint;
use Phalcon\Tests\Fixtures\Container\TestTyped;
use Phalcon\Tests\Fixtures\Container\TestUnionParameter;
use Phalcon\Tests\Fixtures\Container\TestWithConstructorDefaultParameters;
use Phalcon\Tests\Fixtures\Container\TestWithDefaultConstructorParameters;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use Phalcon\Tests\Fixtures\Container\TestWithInterfaceGrandParent;
use Phalcon\Tests\Fixtures\Container\TestWithInterfaceParent;
use Phalcon\Tests\Fixtures\Container\TestWithOptionalConstructorArguments;
use stdClass;

final class ClassDefinitionTest extends AbstractDefinitionBase
{
    /**
     * @return void
     * @throws NotFound
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionAlternativeClass(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->class(stdClass::class);

        $actual = $definition->isInstantiable($this->container);
        $this->assertTrue($actual);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionArgument(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);

        $actual = $definition->hasArgument(0);
        $this->assertFalse($actual);

        $definition->argument(0, 'ten');

        $actual = $definition->hasArgument(0);
        $this->assertTrue($actual);

        $expected = 'ten';
        $actual   = $definition->getArgument(0);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionArgumentOptional(): void
    {
        $definition = new ClassDefinition(TestWithOptionalConstructorArguments::class);

        $definition->argument(0, 'ten');
        $definition->argument(2, ['twenty', 'thirty', 'forty']);

        $expected = TestWithOptionalConstructorArguments::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionArgumentVariadic(): void
    {
        $definition = new ClassDefinition(TestWithOptionalConstructorArguments::class);

        $expected = ['twenty', 'thirty', 'forty'];
        $definition->arguments(
            [
                'ten',
                'fifteen',
                $expected,
            ]
        );
        $actual = $this->actual($definition)->three;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionArgumentVariadicOmitted(): void
    {
        $definition = new ClassDefinition(TestWithOptionalConstructorArguments::class);
        $definition->arguments(
            [
                'ten',
                'fifteen',
            ]
        );

        $expected = [];
        $actual   = $this->actual($definition)->three;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionArgumentVariadicWrong(): void
    {
        $definition = new ClassDefinition(TestWithOptionalConstructorArguments::class);
        $definition->arguments(
            [
                'ten',
                'fifteen',
                'not-an-array',
            ]
        );

        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotAllowed::class,
                    "Variadic argument 2 (\$three) for class definition '" .
                    TestWithOptionalConstructorArguments::class .
                    "' is defined as string, but should be an array " .
                    "of variadic values.",
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionExtenders(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->arguments(['one']);
        $definition->method('append', 'ten');
        $definition->modify(
            function (Container $container, object $obj) {
                $obj->append('twenty');
            }
        );

        $definition->decorate(
            function (Container $container, object $obj) {
                $obj->append('thirty');

                return $obj;
            }
        );

        $definition->property('newProperty', 'newValue');

        $result = $this->actual($definition);

        $expected = 'onetentwentythirty';
        $actual   = $result->one;
        $this->assertSame($expected, $actual);

        $expected = 'newValue';
        $actual   = $result->newProperty;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionFactory(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);

        $definition->factory(
            function ($container) {
                return new stdClass();
            }
        );

        $actual = $definition->isInstantiable($this->container);
        $this->assertTrue($actual);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionInherit(): void
    {
        $definitions = $this->definitions;

        $definitions->{TestWithInterface::class}
            ->argument('one', 'parent')
        ;

        $definitions->{TestWithInterfaceParent::class}
            ->inherit($definitions)
            ->argument('two', 'child')
        ;

        $object = $this->container->new(TestWithInterfaceParent::class);

        $expected = 'parent';
        $actual   = $object->one;
        $this->assertSame($expected, $actual);

        $expected = 'child';
        $actual   = $object->two;
        $this->assertSame($expected, $actual);

        $object = $this->container->new(TestWithInterfaceGrandParent::class);

        $expected = 'parent';
        $actual   = $object->one;
        $this->assertSame($expected, $actual);

        $expected = 'child';
        $actual   = $object->two;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionInheritDisabled(): void
    {
        $this->definitions->{TestWithInterface::class}
            ->argument('one', 'parent')
        ;

        $this->definitions->{TestWithInterfaceParent::class}
            ->inherit(null)
            ->argument('two', 'child')
        ;

        $definition = $this->definitions->{TestWithInterfaceParent::CLASS};

        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotDefined::CLASS,
                    "Required argument 0 (\$one) for class definition '" .
                    TestWithInterfaceParent::class .
                    "' is not defined.",
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionLatestTakesPrecedence(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->arguments(
            [
                0 => 'valbefore',
                'one' => 'valafter',
            ]
        );

        $expected = 'valafter';
        $actual   = $this->actual($definition)->one;
        $this->assertSame($expected, $actual);

        $definition->arguments(
            [
                'one' => 'valbefore',
                0     => 'valafter',
            ]
        );

        $expected = 'valafter';
        $actual   = $this->actual($definition)->one;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionLazy(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->argument(
            0,
            new Call(
                function ($container) {
                    return 'lazy';
                }
            )
        );

        $expected = 'lazy';
        $actual   = $this->actual($definition)->one;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionMissingRequired(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotDefined::class,
                    "Required argument 0 (\$one) for class definition '" .
                    TestWithInterface::class .
                    "' is not defined.",
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionMissingRequiredNullable(): void
    {
        $definition = new ClassDefinition(TestWithDefaultConstructorParameters::class);
        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotInstantiated::class,
                    "Could not instantiate " .
                    TestWithInterface::class,
                ],
                [
                    NotDefined::class,
                    "Required argument 0 (\$one) for class definition '" .
                    TestWithInterface::class .
                    "' is not defined.",
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionMissingUnionType(): void
    {
        $definition = new ClassDefinition(TestUnionParameter::class);
        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotDefined::class,
                    "Union typed argument 0 (\$one) for class definition '" .
                    TestUnionParameter::class .
                    "' is not defined.",
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionNamed(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->argument('one', 'ten');

        $expected = TestWithInterface::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionNamedType(): void
    {
        $definition = new ClassDefinition(TestTyped::class);

        $expected = TestTyped::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);

        $expected = $actual->one;
        $actual   = $this->actual($definition)->one;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionNamedTypeWithDefault(): void
    {
        $definition = new ClassDefinition(TestWithConstructorDefaultParameters::class);
        $definition->argument(1, 'twenty');

        $expected = TestWithConstructorDefaultParameters::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionNoConstructor(): void
    {
        $definition = new ClassDefinition(stdClass::class);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionNoSuchClass(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage("Class 'NoSuchClass' not found.");

        (new ClassDefinition('NoSuchClass'));
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage("Class 'NoSuchClass' not found.");

        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->class('NoSuchClass');
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionNumbered(): void
    {
        $definition = new ClassDefinition(TestWithInterface::class);
        $definition->argument(0, 'ten');

        $expected = TestWithInterface::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotFound
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionSameAsId(): void
    {
        $definition = new ClassDefinition(stdClass::class);
        $definition->class(stdClass::class);

        $expected = stdClass::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsClassDefinitionTypeDoesNotExist(): void
    {
        $definition = new ClassDefinition(TestBadHint::class);
        $this->assertNotInstantiable(
            $definition,
            [
                [
                    NotDefined::class,
                    "Required argument 0 (\$one) for class definition " .
                    "'Phalcon\Tests\Fixtures\Container\TestBadHint'" .
                    " is typehinted as " .
                    "Phalcon\Tests\Fixtures\Container\Nonesuch, " .
                    "which does not exist.",
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionTyped(): void
    {
        $definition = new ClassDefinition(TestTyped::class);
        $definition->argument(
            stdClass::class,
            $this->definitions->new(stdClass::class)
        );

        $expected = TestTyped::class;
        $actual   = $this->actual($definition);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     * @throws NotInstantiated
     */
    public function testContainerDefinitionsClassDefinitionUnionType(): void
    {
        $definition = new ClassDefinition(TestUnionParameter::class);

        $expected = ['arrayval'];
        $definition->argument(0, $expected);

        $actual = $this->actual($definition)->one;
        $this->assertSame($expected, $actual);
    }
}
