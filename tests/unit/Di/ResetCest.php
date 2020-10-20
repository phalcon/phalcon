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

use Phalcon\Di\Di;
use UnitTester;

use function spl_object_hash;

/**
 * Class ResetCest
 *
 * @package Phalcon\Tests\Unit\Di
 */
class ResetCest
{
    /**
     * Unit Tests Phalcon\Di :: reset()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diReset(UnitTester $I)
    {
        $I->wantToTest('Di - reset()');

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        $container = Di::getDefault();

        $expected = spl_object_hash($actual);
        $actual   = spl_object_hash($container);
        $I->assertEquals($expected, $actual);

        // delete it
        Di::reset();

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        // set it again
        Di::setDefault($container);

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);
    }
}
