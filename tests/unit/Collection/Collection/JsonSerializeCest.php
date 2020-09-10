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
use Phalcon\Tests\Fixtures\JsonFixture;
use UnitTester;

class JsonSerializeCest
{
    /**
     * Tests Phalcon\Collection :: jsonSerialize()
     *
     * @param UnitTester $I
     *
     * @since  2020-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function collectionJsonSerialize(UnitTester $I)
    {
        $I->wantToTest('Collection - jsonSerialize()');

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data);

        $I->assertEquals(
            $data,
            $collection->jsonSerialize()
        );

        $data = [
            'one'    => 'two',
            'three'  => 'four',
            'five'   => 'six',
            'object' => new JsonFixture(),
        ];

        $expected = [
            'one'    => 'two',
            'three'  => 'four',
            'five'   => 'six',
            'object' => [
                'one' => 'two',
            ],
        ];

        $collection = new Collection($data);

        $I->assertEquals(
            $expected,
            $collection->jsonSerialize()
        );
    }
}
