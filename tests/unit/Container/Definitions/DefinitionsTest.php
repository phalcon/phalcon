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

use Phalcon\Container\Definitions\ClassDefinition;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Definitions\InterfaceDefinition;
use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Lazy\ArrayValues;
use Phalcon\Container\Lazy\Call;
use Phalcon\Container\Lazy\CallableGet;
use Phalcon\Container\Lazy\CallableNew;
use Phalcon\Container\Lazy\Env;
use Phalcon\Container\Lazy\FunctionCall;
use Phalcon\Container\Lazy\Get;
use Phalcon\Container\Lazy\GetCall;
use Phalcon\Container\Lazy\IncludeFile;
use Phalcon\Container\Lazy\NewCall;
use Phalcon\Container\Lazy\NewInstance;
use Phalcon\Container\Lazy\RequireFile;
use Phalcon\Container\Lazy\StaticCall;
use Phalcon\Tests\Fixtures\Container\TestInterface;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use Phalcon\Tests\AbstractUnitTestCase;

use function uniqid;

final class DefinitionsTest extends AbstractUnitTestCase
{
    protected Definitions $definitions;

    public function setUp(): void
    {
        $this->definitions = new Definitions();
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsAliasedEntries(): void
    {
        $this->definitions->{'one.copy'} = $this->definitions->{TestWithInterface::class};

        $expected = $this->definitions->{TestWithInterface::class};
        $actual   = $this->definitions->{'one.copy'};
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsArray(): void
    {
        $expected = ArrayValues::class;
        $actual   = $this->definitions->array(['one']);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsCall(): void
    {
        $expected = Call::class;
        $actual   = $this->definitions->call(function () {
            return true;
        });
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsCallableGet(): void
    {
        $expected = CallableGet::class;
        $actual   = $this->definitions->callableGet(TestInterface::class);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsCallableNew(): void
    {
        $expected = CallableNew::class;
        $actual   = $this->definitions->callableNew(TestInterface::class);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsClonedEntries(): void
    {
        $this->definitions->{'one.copy'} = clone $this->definitions->{TestWithInterface::class};

        $expected = $this->definitions->{TestWithInterface::class};
        $actual   = $this->definitions->{'one.copy'};
        $this->assertNotSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsCsEnv(): void
    {
        $expected = Env::class;
        $actual   = $this->definitions->csEnv('TEST_ENV');
        $this->assertInstanceOf($expected, $actual);

        $expected = Env::class;
        $actual   = $this->definitions->csEnv('TEST_ENV', 'int');
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsEnv(): void
    {
        $expected = Env::class;
        $actual   = $this->definitions->env('TEST_ENV');
        $this->assertInstanceOf($expected, $actual);

        $expected = Env::class;
        $actual   = $this->definitions->env('TEST_ENV', 'int');
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsFunctionCall(): void
    {
        $expected = FunctionCall::class;
        $actual   = $this->definitions->functionCall(
            'Capsule\Di\fake',
            ['one']
        );
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsGet(): void
    {
        $expected = Get::class;
        $actual   = $this->definitions->get(TestInterface::class);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsGetCall(): void
    {
        $expected = GetCall::class;
        $actual   = $this->definitions->getCall(
            TestInterface::class,
            'getValue'
        );
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsIncludeFile(): void
    {
        $expected = IncludeFile::class;
        $actual   = $this->definitions->include('includeFile.php');
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsMagicGetInterface(): void
    {
        $expected = InterfaceDefinition::class;
        $actual   = $this->definitions->{TestInterface::class};
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsMagicObjects(): void
    {
        // not defined, but exists
        $actual = isset($this->definitions->{TestWithInterface::class});
        $this->assertFalse($actual);

        // define it
        $expected    = ClassDefinition::class;
        $definition1 = $this->definitions->{TestWithInterface::class};
        $this->assertInstanceOf($expected, $definition1);

        // now it is defined
        $actual = isset($this->definitions->{TestWithInterface::class});
        $this->assertTrue($actual);

        // make sure they are shared instances
        $expected    = ClassDefinition::class;
        $definition2 = $this->definitions->{TestWithInterface::class};
        $this->assertInstanceOf($expected, $definition2);

        $this->assertSame($definition1, $definition2);

        // undefine it
        unset($this->definitions->{TestWithInterface::class});

        $actual = isset($this->def->{TestWithInterface::class});
        $this->assertFalse($actual);

        // does not exist
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage(
            "Value definition 'NoSuchClass' not found."
        );

        $this->definitions->NoSuchClass;
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsMagicValues(): void
    {
        $name = uniqid('val');

        // not defined
        $actual = isset($this->definitions->$name);
        $this->assertFalse($actual);

        $this->definitions->$name = 'ten';

        $actual = isset($this->definitions->$name);
        $this->assertTrue($actual);

        $expected = 'ten';
        $actual   = $this->definitions->$name;
        $this->assertSame($expected, $actual);

        unset($this->definitions->$name);

        $actual = isset($this->definitions->$name);
        $this->assertFalse($actual);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage(
            "Value definition '$name' not found."
        );

        $this->definitions->$name;
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsNamedEntries(): void
    {
        $this->definitions->one = new ClassDefinition(TestWithInterface::class);

        $expected = ClassDefinition::class;
        $actual   = $this->definitions->one;
        $this->assertInstanceOf($expected, $actual);

        $this->definitions->two = new ClassDefinition(TestWithInterface::class);

        $expected = ClassDefinition::class;
        $actual   = $this->definitions->two;
        $this->assertInstanceOf($expected, $actual);

        $expected = $this->definitions->one;
        $actual   = $this->definitions->two;
        $this->assertNotSame($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsNew(): void
    {
        $expected = NewInstance::class;
        $actual   = $this->definitions->new(TestInterface::class);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsNewCall(): void
    {
        $expected = NewCall::class;
        $actual   = $this->definitions->newCall(
            TestInterface::class,
            'getValue'
        );
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsRequire(): void
    {
        $expected = RequireFile::class;
        $actual   = $this->definitions->require('includeFile.php');
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerDefinitionsDefinitionsStaticCall(): void
    {
        $expected = StaticCall::class;
        $actual   = $this->definitions->staticCall(
            TestWithInterface::class,
            'staticFake',
            ['bar']
        );
        $this->assertInstanceOf($expected, $actual);
    }
}
