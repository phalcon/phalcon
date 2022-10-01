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

class RemoveCest
{
    /**
     * Tests Phalcon\Support\Registry :: remove()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function registryRemove(UnitTester $I)
    {
        $I->wantToTest('Registry - remove()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);

        $I->assertSame(
            $data,
            $registry->toArray()
        );


        $registry->remove('five');

        $I->assertSame(
            [
                'one'   => 'two',
                'three' => 'four',
            ],
            $registry->toArray()
        );


        $registry->remove('FIVE');

        $I->assertSame(
            [
                'one'   => 'two',
                'three' => 'four',
            ],
            $registry->toArray()
        );


        $registry->init($data);

        unset($registry['five']);

        $I->assertSame(
            [
                'one'   => 'two',
                'three' => 'four',
            ],
            $registry->toArray()
        );


        $registry->init($data);

        $registry->offsetUnset('five');

        $I->assertSame(
            [
                'one'   => 'two',
                'three' => 'four',
            ],
            $registry->toArray()
        );
    }
}
