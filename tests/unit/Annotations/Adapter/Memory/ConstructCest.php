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

namespace Phalcon\Tests\Unit\Annotations\Adapter\Memory;

use Phalcon\Annotations\Adapter\AdapterInterface;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reflection;
use TestClass;
use UnitTester;
use User\TestClassNs;

use function dataDir;
use function is_object;

class ConstructCest
{
    /**
     * Tests Phalcon\Annotations\Adapter\Memory :: __construct()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAdapterMemoryConstruct(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Memory - __construct()');

        $adapter = new Memory();

        $expected = AdapterInterface::class;
        $actual   = $adapter;
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function annotationsAdapterMemory(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Memory - __construct()');

        $I->seeFileFound(
            dataDir('fixtures/Annotations/TestClass.php')
        );

        $I->seeFileFound(
            dataDir('fixtures/Annotations/TestClassNs.php')
        );

        require_once dataDir('fixtures/Annotations/TestClass.php');
        require_once dataDir('fixtures/Annotations/TestClassNs.php');

        $adapter = new Memory();

        $classAnnotations = $adapter->get(TestClass::class);

        $actual = is_object($classAnnotations);
        $I->assertTrue($actual);

        $expected = Reflection::class;
        $actual   = $classAnnotations;
        $I->assertInstanceOf($expected, $actual);

        $expected = Collection::class;
        $actual   = $classAnnotations->getClassAnnotations();
        $I->assertInstanceOf($expected, $actual);

        $classAnnotations = $adapter->get(TestClassNs::class);

        $actual = is_object($classAnnotations);
        $I->assertTrue($actual);

        $expected = Reflection::class;
        $actual   = $classAnnotations;
        $I->assertInstanceOf($expected, $actual);

        $expected = Collection::class;
        $actual   = $classAnnotations->getClassAnnotations();
        $I->assertInstanceOf($expected, $actual);


        $property = $adapter->getProperty(
            TestClass::class,
            'testProp1'
        );

        $actual = is_object($property);
        $I->assertTrue($actual);

        $expected = Collection::class;
        $actual   = $property;
        $I->assertInstanceOf($expected, $actual);

        $expected = 4;
        $actual   = $property->count();
        $I->assertSame($expected, $actual);
    }
}
