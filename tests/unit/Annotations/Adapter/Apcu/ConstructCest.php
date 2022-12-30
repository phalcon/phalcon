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

namespace Phalcon\Tests\Unit\Annotations\Adapter\Apcu;

use Phalcon\Annotations\Adapter\AdapterInterface;
use Phalcon\Annotations\Adapter\Apcu;
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
     * executed before each test
     */
    public function _before(UnitTester $I)
    {
        $I->checkExtensionIsLoaded('apcu');

        require_once dataDir('fixtures/Annotations/TestClass.php');
        require_once dataDir('fixtures/Annotations/TestClassNs.php');
    }
    /**
     * Tests Phalcon\Annotations\Adapter\Apcu :: __construct()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAdapterApcuConstruct(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Apcu - __construct()');

        $adapter = new Apcu(
            [
                'prefix'   => 'nova_prefix',
                'lifetime' => 3600,
            ]
        );

        $expected = AdapterInterface::class;
        $actual   = $adapter;
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function annotationsAdapterApcu(UnitTester $I)
    {
        $adapter = new Apcu();

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

        $expected = 4;
        $actual   = $property->count();
        $I->assertSame($expected, $actual);
    }
}
