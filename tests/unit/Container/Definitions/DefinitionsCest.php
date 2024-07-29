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
use UnitTester;

class DefinitionsCest
{
    protected Definitions $definitions;

    public function _before(): void
    {
        $this->definitions = new Definitions();
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsNamedEntries(UnitTester $I): void
    {
        $this->definitions->one = new ClassDefinition(TestWithInterface::class);

        $expected = ClassDefinition::class;
        $actual   = $this->definitions->one;
        $I->assertInstanceOf($expected, $actual);

        $this->definitions->two = new ClassDefinition(TestWithInterface::class);

        $expected = ClassDefinition::class;
        $actual   = $this->definitions->two;
        $I->assertInstanceOf($expected, $actual);

        $expected = $this->definitions->one;
        $actual   = $this->definitions->two;
        $I->assertNotSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsAliasedEntries(UnitTester $I): void
    {
        $this->definitions->{'one.copy'} = $this->definitions->{TestWithInterface::class};

        $expected = $this->definitions->{TestWithInterface::class};
        $actual   = $this->definitions->{'one.copy'};
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsClonedEntries(UnitTester $I): void
    {
        $this->definitions->{'one.copy'} = clone $this->definitions->{TestWithInterface::class};

        $expected = $this->definitions->{TestWithInterface::class};
        $actual   = $this->definitions->{'one.copy'};
        $I->assertNotSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsMagicObjects(UnitTester $I): void
    {
        // not defined, but exists
        $actual = isset($this->definitions->{TestWithInterface::class});
        $I->assertFalse($actual);

        // define it
        $expected    = ClassDefinition::class;
        $definition1 = $this->definitions->{TestWithInterface::class};
        $I->assertInstanceOf($expected, $definition1);

        // now it is defined
        $actual = isset($this->definitions->{TestWithInterface::class});
        $I->assertTrue($actual);

        // make sure they are shared instances
        $expected    = ClassDefinition::class;
        $definition2 = $this->definitions->{TestWithInterface::class};
        $I->assertInstanceOf($expected, $definition2);

        $I->assertSame($definition1, $definition2);

        // undefine it
        unset($this->definitions->{TestWithInterface::class});

        $actual = isset($this->def->{TestWithInterface::class});
        $I->assertFalse($actual);

        // does not exist
        $I->expectThrowable(
            new NotFound("Value definition 'NoSuchClass' not found."),
            function () {
                $this->definitions->NoSuchClass;
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsMagicValues(UnitTester $I): void
    {
        // not defined
        $actual = isset($this->definitions->one);
        $I->assertFalse($actual);

        $this->definitions->one = 'ten';

        $actual = isset($this->definitions->one);
        $I->assertTrue($actual);

        $expected = 'ten';
        $actual   = $this->definitions->one;
        $I->assertSame($expected, $actual);

        unset($this->definitions->one);

        $actual = isset($this->definitions->one);
        $I->assertFalse($actual);

        $I->expectThrowable(
            new NotFound("Value definition 'one' not found."),
            function () {
                $this->definitions->one;
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsMagicGetInterface(UnitTester $I): void
    {
        $expected = InterfaceDefinition::class;
        $actual   = $this->definitions->{TestInterface::class};
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsCall(UnitTester $I): void
    {
        $expected = Call::class;
        $actual   = $this->definitions->call(function () {
            return true;
        });
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsCallableGet(UnitTester $I): void
    {
        $expected = CallableGet::class;
        $actual   = $this->definitions->callableGet(TestInterface::class);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsCallableNew(UnitTester $I): void
    {
        $expected = CallableNew::class;
        $actual   = $this->definitions->callableNew(TestInterface::class);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsCsEnv(UnitTester $I): void
    {
        $expected = Env::class;
        $actual   = $this->definitions->csEnv('TEST_ENV');
        $I->assertInstanceOf($expected, $actual);

        $expected = Env::class;
        $actual   = $this->definitions->csEnv('TEST_ENV', 'int');
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsEnv(UnitTester $I): void
    {
        $expected = Env::class;
        $actual   = $this->definitions->env('TEST_ENV');
        $I->assertInstanceOf($expected, $actual);

        $expected = Env::class;
        $actual   = $this->definitions->env('TEST_ENV', 'int');
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsArray(UnitTester $I): void
    {
        $expected = ArrayValues::class;
        $actual   = $this->definitions->array(['one']);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsFunctionCall(UnitTester $I): void
    {
        $expected = FunctionCall::class;
        $actual   = $this->definitions->functionCall(
            'Capsule\Di\fake',
            ['one']
        );
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsGet(UnitTester $I): void
    {
        $expected = Get::class;
        $actual   = $this->definitions->get(TestInterface::class);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsGetCall(UnitTester $I): void
    {
        $expected = GetCall::class;
        $actual   = $this->definitions->getCall(
            TestInterface::class,
            'getValue'
        );
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsIncludeFile(UnitTester $I): void
    {
        $expected = IncludeFile::class;
        $actual   = $this->definitions->include('includeFile.php');
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsNew(UnitTester $I): void
    {
        $expected = NewInstance::class;
        $actual   = $this->definitions->new(TestInterface::class);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsNewCall(UnitTester $I): void
    {
        $expected = NewCall::class;
        $actual   = $this->definitions->newCall(
            TestInterface::class,
            'getValue'
        );
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsRequire(UnitTester $I): void
    {
        $expected = RequireFile::class;
        $actual   = $this->definitions->require('includeFile.php');
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerDefinitionsDefinitionsStaticCall(UnitTester $I): void
    {
        $expected = StaticCall::class;
        $actual   = $this->definitions->staticCall(
            TestWithInterface::class,
            'staticFake',
            ['bar']
        );
        $I->assertInstanceOf($expected, $actual);
    }
}
