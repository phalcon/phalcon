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
use function spl_object_hash;

class GetSetSharedCest
{
    /**
     * Tests Phalcon\Di :: getShared()/setShared()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diGetShared(UnitTester $I)
    {
        $I->wantToTest('Di - getShared()');

        $container = new Di();

        $class = new Escaper();
        $container->setShared('escaper', $class);

        $object = $container->getShared('escaper');

        $expected = spl_object_hash($class);
        $actual   = spl_object_hash($object);
        $I->assertInstanceOf($expected, $actual);

        $objectTwo = $container->getShared('escaper');
        $actual   = spl_object_hash($objectTwo);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * Tests Phalcon\Di :: getShared() - set
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diGetSetSharedSet(UnitTester $I)
    {
        $I->wantToTest('Di - getShared() - set');

        $container = new Di();

        $class = new Escaper();
        $container->set('escaper', $class);

        $object = $container->getShared('escaper');

        $expected = spl_object_hash($class);
        $actual   = spl_object_hash($object);
        $I->assertInstanceOf($expected, $actual);

        $objectTwo = $container->getShared('escaper');
        $actual   = spl_object_hash($objectTwo);
        $I->assertInstanceOf($expected, $actual);


        $expected = new Escaper();
        $I->assertEquals($expected, $actual);

        $container->set('collection', Collection::class, true);
        $actual = $container->getShared('collection');

        $I->assertInstanceOf(Collection::class, $actual);

        $actual   = $container->getShared('crypt');
        $expected = new Crypt();

        $I->assertEquals($expected, $actual);
    }
}
