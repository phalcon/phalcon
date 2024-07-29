<?php

/**
 * This file is part of the Phalcon Framework.
 * (c) Phalcon Team <team@phalcon.io>
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Factory;

use Phalcon\Tests\Fixtures\Factory\TestFactory;
use Throwable;
use UnitTester;

class ServicesCest
{
    /**
     * Tests Phalcon\Factory\AbstractFactory :: getServices() - empty
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function factoryGetServices(UnitTester $I)
    {
        $factory = new TestFactory([]);
        $I->wantToTest('Factory\AbstractFactory - getServices - empty');
        $I->assertEmpty($factory->services());
    }

    /**
     * Tests Phalcon\Factory\AbstractFactory :: getServices() - does not exist
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function factoryGetServicesException(UnitTester $I)
    {
        $factory = new TestFactory([]);
        $I->wantToTest('Factory\AbstractFactory - getServices - does not exist');
        try {
            $factory->service('test');
        } catch (Throwable $exc) {
            // Weird bug reports \Phalcon\Factory\Exception as \Exception
            $I->assertEquals('Service test is not registered', $exc->getMessage());
        }
    }
}
