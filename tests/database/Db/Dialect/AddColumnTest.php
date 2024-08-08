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

namespace Phalcon\Tests\Database\Db\Dialect;

use Phalcon\Db\Column;
use Phalcon\Db\Dialect\Mysql;
use Phalcon\Db\Dialect\Postgresql;
use Phalcon\Db\Dialect\Sqlite;
use Phalcon\Tests\DatabaseTestCase;

final class AddColumnTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumn(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_INTEGER,
            'isNumeric'     => true,
            'size'          => 11,
            'scale'         => 0,
            'default'       => 13,
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => true,
            'primary'       => true,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_INT,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialectsTimestamp
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumnDefaultTimestamp(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_VARCHAR,
            'isNumeric'     => false,
            'size'          => 10,
            'scale'         => null,
            'default'       => 'CURRENT_TIMESTAMP',
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => false,
            'primary'       => false,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_STR,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialectsInt
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumnDefaultInt(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_INTEGER,
            'isNumeric'     => true,
            'size'          => 10,
            'scale'         => 0,
            'default'       => 13,
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => false,
            'primary'       => false,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_INT,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialectsFloat
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumnDefaultFloat(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_DOUBLE,
            'isNumeric'     => true,
            'size'          => 10,
            'scale'         => 2,
            'default'       => 13.34,
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => false,
            'primary'       => false,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_DECIMAL,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialectsString
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumnDefaultString(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_VARCHAR,
            'isNumeric'     => false,
            'size'          => 10,
            'scale'         => null,
            'default'       => 'test',
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => false,
            'primary'       => false,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_STR,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addColumn
     *
     * @dataProvider getDialectsNull
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddColumnDefaultNull(
        string $dialectClass,
        string $expected
    ): void {
        $dialect = new $dialectClass();

        $options = [
            'type'          => Column::TYPE_VARCHAR,
            'isNumeric'     => false,
            'size'          => 10,
            'scale'         => null,
            'default'       => 'NULL',
            'unsigned'      => false,
            'notNull'       => true,
            'autoIncrement' => false,
            'primary'       => false,
            'first'         => true,
            'after'         => null,
            'bindType'      => Column::BIND_PARAM_STR,
        ];

        $column = new Column('field_primary', $options);

        $actual  = $dialect->addColumn('table', 'schema', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` INT(11) NOT NULL AUTO_INCREMENT FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsTimestamp(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` VARCHAR(10) NOT NULL DEFAULT CURRENT_TIMESTAMP FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsInt(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` INT(10) NOT NULL DEFAULT 13 FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsFloat(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` DOUBLE(10,2) NOT NULL DEFAULT 13.34 FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsString(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` VARCHAR(10) NOT NULL DEFAULT "test" FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsNull(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` ADD `field_primary` VARCHAR(10) NOT NULL DEFAULT NULL FIRST'

            ],
//            [Postgresql::class],
//            [Sqlite::class],
        ];
    }
}
