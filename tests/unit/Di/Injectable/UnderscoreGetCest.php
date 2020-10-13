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

namespace Phalcon\Tests\Unit\Di\Injectable;

use Phalcon\Di\Di;
use Phalcon\Tests\Fixtures\Di\InjectableComponent;
use stdClass;
use UnitTester;

class UnderscoreGetCest
{
    /**
     * Unit Tests Phalcon\Di\Injectable :: __get()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diInjectableUnderscoreGet(UnitTester $I)
    {
        $I->wantToTest('Di\Injectable - __get()');

        Di::reset();
        $container = new Di();

        $stdClass = function () {
            return new stdClass();
        };

        $container->set('std', $stdClass);
        $container->set('component', InjectableComponent::class);

        $component = $container->get('component');
        $actual    = $component->getDI();
        $I->assertEquals($container, $actual);

        $class  = stdClass::class;
        $actual = $component->std;
        $I->assertInstanceOf($class, $actual);
    }
}
