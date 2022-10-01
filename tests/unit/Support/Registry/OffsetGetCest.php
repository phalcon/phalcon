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

class OffsetGetCest
{
    /**
     * Unit Tests Phalcon\Support\Registry :: offsetGet()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-25
     */
    public function registryOffsetGet(UnitTester $I)
    {
        $I->wantToTest('Registry - offsetGet()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);

        $expected = 'four';

        $I->assertSame(
            $expected,
            $registry['three']
        );

        $I->assertSame(
            $expected,
            $registry->offsetGet('three')
        );
    }
}
