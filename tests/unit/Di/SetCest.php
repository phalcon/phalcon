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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Collection\Collection;
use Phalcon\Di\Di;
use Phalcon\Escaper\Escaper;
use UnitTester;

class SetCest
{
    /**
     * Unit Tests Phalcon\Di :: set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diSet(UnitTester $I)
    {
        $I->wantToTest('Di - set()');

        $container = new Di();

        // set non shared service
        $container->set('escaper', Escaper::class);

        $class  = Escaper::class;
        $actual = $container->get('escaper');
        $I->assertInstanceOf($class, $actual);

        $escaper = $container->getService('escaper');
        $actual  = $escaper->isShared();
        $I->assertFalse($actual);

        // set shared service
        $container->set('collection', Collection::class, true);

        $class  = Collection::class;
        $actual = $container->get('collection');
        $I->assertInstanceOf($class, $actual);

        $collection = $container->getService('collection');
        $actual     = $collection->isShared();
        $I->assertTrue($actual);

        // testing closure
        $returnValue = "Closure Test!";
        $container->set(
            'closure',
            function () use ($returnValue) {
                return $returnValue;
            }
        );

        $actual = $container->get('closure');
        $I->assertEquals($returnValue, $actual);
    }
}
