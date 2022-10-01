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

class InitCest
{
    /**
     * Tests Phalcon\Support\Registry :: init()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function registryInit(UnitTester $I)
    {
        $I->wantToTest('Registry - init()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry();

        $I->assertSame(
            0,
            $registry->count()
        );

        $registry->init($data);

        $I->assertSame(
            $data,
            $registry->toArray()
        );
    }
}
