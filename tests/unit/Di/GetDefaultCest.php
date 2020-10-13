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

class GetDefaultCest
{
    /**
     * Unit Tests Phalcon\Di :: getDefault()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diGetDefault(UnitTester $I)
    {
        $I->wantToTest('Di - getDefault()');

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        $container = Di::getDefault();
        $class = Di::class;
        $I->assertInstanceOf($class, $container);

        // delete it
        Di::reset();

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        // set it again
        Di::setDefault($container);

        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);
    }
}
