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

class ConstructCest
{
    /**
     * Tests Phalcon\Di :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diConstruct(UnitTester $I)
    {
        $I->wantToTest('Di - __construct()');

        $class = Di::class;
        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);

        $actual = Di::getDefault();
        $I->assertInstanceOf($class, $actual);
    }
}
