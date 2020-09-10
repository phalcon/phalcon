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

namespace Phalcon\Tests\Unit\Collection\Collection;

use Phalcon\Collection\Collection;
use UnitTester;

class SerializeCest
{
    /**
     * Tests Phalcon\Collection :: serialize()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function collectionSerialize(UnitTester $I)
    {
        $I->wantToTest('Collection - serialize()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data);

        $I->assertEquals(
            serialize($data),
            $collection->serialize()
        );
    }
}
