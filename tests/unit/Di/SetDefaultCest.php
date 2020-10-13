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

class SetDefaultCest
{
    /**
     * Unit Tests Phalcon\Di :: setDefault()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diSetDefault(UnitTester $I)
    {
        $I->wantToTest('Di - setDefault()');

        Di::reset();

        $class  = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        $container = Di::getDefault();

        $expected = spl_object_hash($actual);
        $actual   = spl_object_hash($container);
        $I->assertEquals($expected, $actual);

        $new = new Di();

        Di::setDefault($new);

        $expected = spl_object_hash($new);
        $actual   = spl_object_hash(Di::getDefault());
        $I->assertEquals($expected, $actual);
    }
}
