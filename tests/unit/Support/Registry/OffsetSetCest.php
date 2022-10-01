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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use UnitTester;

class OffsetSetCest
{
    /**
     * Unit Tests Phalcon\Support\Registry :: offsetSet()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function registryOffsetSet(UnitTester $I)
    {
        $I->wantToTest('Registry - offsetSet()');

        $registry = new Registry();


        $registry->offsetSet('three', 123);

        $I->assertSame(
            123,
            $registry->get('three')
        );


        $registry['three'] = 456;

        $I->assertSame(
            456,
            $registry->get('three')
        );
    }
}
