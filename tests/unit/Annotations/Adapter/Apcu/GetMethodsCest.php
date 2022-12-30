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
use TestClass;
use UnitTester;

use function dataDir;

class GetMethodsCest
{
    /**
     * Tests Phalcon\Annotations\Adapter\Apcu :: getMethods()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAdapterApcuGetMethods(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Apcu - getMethods()');

        require_once dataDir('fixtures/Annotations/TestClass.php');

        $adapter = new Apcu(
            [
                'prefix'   => 'nova_prefix',
                'lifetime' => 3600,
            ]
        );

        $methodAnnotations = $adapter->getMethods(TestClass::class);

        $expected = [
            'testMethod1',
            'testMethod3',
            'testMethod4',
            'testMethod5',
        ];
        $actual   = array_keys($methodAnnotations);
        $I->assertSame($expected, $actual);

        foreach ($methodAnnotations as $methodAnnotation) {
            $expected = Collection::class;
            $actual   = $methodAnnotation;
            $I->assertInstanceOf($expected, $actual);
        }
    }
}
