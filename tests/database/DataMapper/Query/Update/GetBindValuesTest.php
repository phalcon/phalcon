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

namespace Phalcon\Tests\Database\DataMapper\Query\Update;

use PDO;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\DatabaseTestCase;

final class GetBindValuesTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Update :: getBindValues()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryUpdateGetBindValues(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $update     = $factory->newUpdate($connection);

        $expected = [];
        $actual   = $update->getBindValues();
        $this->assertEquals($expected, $actual);

        $update
            ->bindValues(
                [
                    'one'   => 100,
                    'two'   => null,
                    'three' => true,
                    'four'  => [1, 2, 3],
                ]
            )
        ;

        $expected = [
            'one'   => [100, PDO::PARAM_INT],
            'two'   => [null, PDO::PARAM_NULL],
            'three' => [true, PDO::PARAM_BOOL],
            'four'  => [[1, 2, 3], PDO::PARAM_STR],
        ];
        $actual   = $update->getBindValues();
        $this->assertEquals($expected, $actual);

        $update
            ->bindValues(
                [
                    'five' => 'active',
                ]
            )
        ;

        $expected = [
            'one'   => [100, PDO::PARAM_INT],
            'two'   => [null, PDO::PARAM_NULL],
            'three' => [true, PDO::PARAM_BOOL],
            'four'  => [[1, 2, 3], PDO::PARAM_STR],
            'five'  => ['active', PDO::PARAM_STR],
        ];
        $actual   = $update->getBindValues();
        $this->assertEquals($expected, $actual);
    }
}
