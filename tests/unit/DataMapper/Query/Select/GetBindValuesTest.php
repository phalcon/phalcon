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

namespace Phalcon\Tests\Unit\DataMapper\Query\Select;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\DataMapper\Query\QueryFactory;

final class GetBindValuesTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: getBindValues()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQuerySelectGetBindValues(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        /**
         * BindValues - empty
         */
        $expected = [];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);

        /**
         * BindValues
         */
        $select->bindValues(
            [
                'one'   => 'two',
                'three' => 'four',
            ]
        );
        $expected = [
            'one'   => ['two', PDO::PARAM_STR],
            'three' => ['four', PDO::PARAM_STR],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);

        /**
         * BindValues - append
         */
        $select->bindValues(
            [
                'five' => 'six',
            ]
        );
        $expected = [
            'one'   => ['two', PDO::PARAM_STR],
            'three' => ['four', PDO::PARAM_STR],
            'five'  => ['six', PDO::PARAM_STR],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);

        /**
         * BindValue
         */
        $select->bindValue("seven", 8, PDO::PARAM_INT);
        $expected = [
            'one'   => ['two', PDO::PARAM_STR],
            'three' => ['four', PDO::PARAM_STR],
            'five'  => ['six', PDO::PARAM_STR],
            'seven' => [8, PDO::PARAM_INT],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);

        /**
         * BindInline
         */
        $select->bindInline(false, PDO::PARAM_BOOL);
        $expected = [
            'one'   => ['two', PDO::PARAM_STR],
            'three' => ['four', PDO::PARAM_STR],
            'five'  => ['six', PDO::PARAM_STR],
            'seven' => [8, PDO::PARAM_INT],
            '__1__' => [false, PDO::PARAM_BOOL],
        ];
        $actual   = $select->getBindValues();
        $this->assertEquals($expected, $actual);
    }
}
