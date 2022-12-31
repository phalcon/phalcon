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

use Phalcon\Annotations\Adapter\Apcu;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reflection;
use TestClass;
use UnitTester;

class GetCest
{
    /**
     * Tests Phalcon\Annotations\Adapter\Apcu :: get()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAdapterApcuGet(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Apcu - get()');

        require_once dataDir('fixtures/Annotations/TestClass.php');

        $adapter = new Apcu(
            [
                'prefix'   => 'nova_prefix',
                'lifetime' => 3600,
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
    }
}
