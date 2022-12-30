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

namespace Phalcon\Tests\Unit\Annotations\Adapter\Stream;

use Phalcon\Annotations\Adapter\Stream;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reflection;
use TestClass;
use UnitTester;

use function dataDir;
use function outputDir;

class GetCest
{
    /**
     * Tests Phalcon\Annotations\Adapter\Stream :: get()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function annotationsAdapterStreamGet(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Stream - get()');

        require_once dataDir('fixtures/Annotations/TestClass.php');

        $adapter = new Stream(
            [
                'annotationsDir' => outputDir('tests/annotations/'),
            ]
        );


        $classAnnotations = $adapter->get(TestClass::class);

        $actual = is_object($classAnnotations);
        $I->assertTrue($actual);

        $expected = Reflection::class;
        $actual   = $classAnnotations;
        $I->assertInstanceOf($expected, $actual);

        $expected = Collection::class;
        $actual   = $classAnnotations->getClassAnnotations();
        $I->assertInstanceOf($expected, $actual);

        $I->safeDeleteFile('testclass.php');
    }
}
