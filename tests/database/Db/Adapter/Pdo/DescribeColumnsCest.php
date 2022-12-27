<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Adapter\Pdo;

use DatabaseTester;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Column;
use Phalcon\Tests\Fixtures\Migrations\ComplexDefaultMigration;
use Phalcon\Tests\Fixtures\Migrations\DialectMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

class DescribeColumnsCest
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @param DatabaseTester $I
     *
     * @return void
     */
    public function _before(DatabaseTester $I): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase($I);
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: describeColumns() - supported
     *
     * @param DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-20
     *
     * @group  mysql
     */
    public function dbAdapterPdoDescribeColumnsSupported(DatabaseTester $I)
    {
        $I->wantToTest('Db\Adapter\Pdo - describeColumns() - supported');

        /** @var Mysql $db */
        $db        = $this->container->get('db');
        $migration = new DialectMigration($I->getConnection());
        $columns   = $db->describeColumns($migration->getTable());

        $expected = 40;
        $I->assertCount($expected, $columns);

        $expected = Column::class;
        $actual   = $columns[1];
        $I->assertInstanceOf($expected, $actual);

        foreach ($columns as $index => $column) {
            $expected = $this->getExpected($index);
            $actual   = $this->getActual($column);

            $I->assertEquals($expected, $actual);
        }
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: describeColumns()
     *
     * @param DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-02
     *
     * @group  mysql
     */
    public function dbAdapterPdoDescribeColumnsOnUpdate(DatabaseTester $I)
    {
        $I->wantToTest('Db\Adapter\Pdo - describeColumns()');

        $db        = $this->container->get('db');
        $now       = date('Y-m-d H:i:s');
        $migration = new ComplexDefaultMigration($I->getConnection());
        $migration->insert(1, $now, $now);

        $columns = $db->describeColumns($migration->getTable());

        $I->assertSame('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP', $columns[2]->getDefault());
        $I->assertSame('NULL on update CURRENT_TIMESTAMP', $columns[3]->getDefault());
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: describeColumns()
     *
     * @param DatabaseTester $I
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-03-09
     *
     * @group  pgsql
     */
    public function dbAdapterPdoDescribeColumnsDefaultPostgres(DatabaseTester $I)
    {
        $I->wantToTest('Db\Adapter\Pdo - describeColumns() - CheckPostgres Default value');

        $db        = $this->container->get('db');
        $now       = date('Y-m-d H:i:s');
        $migration = new ComplexDefaultMigration($I->getConnection());
        $migration->insert(1, $now, $now);

        $columns = $db->describeColumns($migration->getTable());

        $I->assertRegexp('/CURRENT_TIMESTAMP|now\(\)/i', $columns[1]->getDefault());
        $I->assertRegexp('/CURRENT_TIMESTAMP|now\(\)/i', $columns[2]->getDefault());
    }

    /**
     * @param Column $column
     *
     * @return array
     */
    private function getActual(Column $column): array
    {
        return [
            'getAfterPosition' => $column->getAfterPosition(),
            'getBindType'      => $column->getBindType(),
            'getComment'       => $column->getComment(),
            'getDefault'       => $column->getDefault(),
            'getName'          => $column->getName(),
            'getScale'         => $column->getScale(),
            'getSize'          => $column->getSize(),
            'getType'          => $column->getType(),
            'getTypeReference' => $column->getTypeReference(),
            'getTypeValues'    => $column->getTypeValues(),
            'hasDefault'       => $column->hasDefault(),
            'isAutoIncrement'  => $column->isAutoIncrement(),
            'isFirst'          => $column->isFirst(),
            'isNotNull'        => $column->isNotNull(),
            'isNumeric'        => $column->isNumeric(),
            'isPrimary'        => $column->isPrimary(),
            'isUnsigned'       => $column->isUnsigned(),
        ];
    }

    private function getExpected(int $index): array
    {
        // 0  getAfterPosition
        // 1  getBindType
        // 2  getComment
        // 3  getDefault
        // 4  getName
        // 5  getScale
        // 6  getSize
        // 7  getType
        // 8  getTypeReference
        // 9  getTypeValues
        // 10 hasDefault
        // 11 isAutoIncrement
        // 12 isFirst
        // 13 isNotNull
        // 14 isNumeric
        // 15 isPrimary
        // 16 isUnsigned

        $metadata = [
            // field_primary            int auto_increment primary key,
            0  => [
                'getAfterPosition' => "",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_primary',
                'getScale'         => 0,
                'getSize'          => 11,
                'getType'          => Column::TYPE_INTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => true,
                'isFirst'          => true,
                'isNotNull'        => true,
                'isNumeric'        => true,
                'isPrimary'        => true,
                'isUnsigned'       => false,
            ],
            // field_blob               blob                                        null,
            1  => [
                'getAfterPosition' => "field_primary",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_blob',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_BLOB,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_binary             binary(10)                                  null,
            2  => [
                'getAfterPosition' => "field_blob",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_binary',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_BINARY,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_bit                bit(10)                                     null,
            3  => [
                'getAfterPosition' => "field_binary",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_bit',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_BIT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_bit_default        bit(10)       default b'1'                  null,
            4  => [
                'getAfterPosition' => "field_bit",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "b'1'",
                'getName'          => 'field_bit_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_BIT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_bigint             bigint        unsigned                      null,
            5  => [
                'getAfterPosition' => "field_bit_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_bigint',
                'getScale'         => 0,
                'getSize'          => 20,
                'getType'          => Column::TYPE_BIGINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => true,
            ],
            // field_bigint_default     bigint        default 1                     null,
            6  => [
                'getAfterPosition' => "field_bigint",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => 1,
                'getName'          => 'field_bigint_default',
                'getScale'         => 0,
                'getSize'          => 20,
                'getType'          => Column::TYPE_BIGINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_boolean            tinyint(1)    unsigned                      null,
            7  => [
                'getAfterPosition' => "field_bigint_default",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_boolean',
                'getScale'         => 0,
                'getSize'          => 1,
                'getType'          => Column::TYPE_TINYINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => true,
            ],
            // field_boolean_default    tinyint(1)    default 1                     null,
            8  => [
                'getAfterPosition' => "field_boolean",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "1",
                'getName'          => 'field_boolean_default',
                'getScale'         => 0,
                'getSize'          => 1,
                'getType'          => Column::TYPE_TINYINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_char               char(10)                                    null,
            9  => [
                'getAfterPosition' => "field_boolean_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_char',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_CHAR,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_char_default       char(10)      default 'ABC'                 null,
            10 => [
                'getAfterPosition' => "field_char",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "ABC",
                'getName'          => 'field_char_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_CHAR,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_decimal            decimal(10,4)                               null,
            11 => [
                'getAfterPosition' => "field_char_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_decimal',
                'getScale'         => 4,
                'getSize'          => 10,
                'getType'          => Column::TYPE_DECIMAL,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_decimal_default    decimal(10,4) default 14.5678               null,
            12  => [
                'getAfterPosition' => "field_decimal",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "14.5678",
                'getName'          => 'field_decimal_default',
                'getScale'         => 4,
                'getSize'          => 10,
                'getType'          => Column::TYPE_DECIMAL,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_enum               enum('xs', 's', 'm', 'l', 'xl', 'internal') null,
            13  => [
                'getAfterPosition' => "field_decimal_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_enum',
                'getScale'         => 0,
                'getSize'          => "'xs','s','m','l','xl','internal'",
                'getType'          => Column::TYPE_ENUM,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_integer            int(10)                                     null,
            14  => [
                'getAfterPosition' => "field_enum",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_integer',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_INTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_integer_default    int(10)       default 1                     null,
            15  => [
                'getAfterPosition' => "field_integer",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "1",
                'getName'          => 'field_integer_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_INTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_json               json                                        null,
            16  => [
                'getAfterPosition' => "field_integer_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_json',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_JSON,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_float              float(10,4)                                 null,
            17  => [
                'getAfterPosition' => "field_json",
                'getBindType'      => Column::BIND_PARAM_DECIMAL,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_float',
                'getScale'         => 4,
                'getSize'          => 10,
                'getType'          => Column::TYPE_FLOAT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_float_default      float(10,4)   default 14.5678               null,
            18  => [
                'getAfterPosition' => "field_float",
                'getBindType'      => Column::BIND_PARAM_DECIMAL,
                'getComment'       => "",
                'getDefault'       => "14.5678",
                'getName'          => 'field_float_default',
                'getScale'         => 4,
                'getSize'          => 10,
                'getType'          => Column::TYPE_FLOAT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_date               date                                        null,
            19  => [
                'getAfterPosition' => "field_float_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_date',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_DATE,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_date_default       date          default '2018-10-01'          null,
            20  => [
                'getAfterPosition' => "field_date",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "2018-10-01",
                'getName'          => 'field_date_default',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_DATE,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_datetime           datetime                                    null,
            21  => [
                'getAfterPosition' => "field_date_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_datetime',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_DATETIME,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_datetime_default   datetime      default '2018-10-01 12:34:56' null,
            22  => [
                'getAfterPosition' => "field_datetime",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "2018-10-01 12:34:56",
                'getName'          => 'field_datetime_default',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_DATETIME,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_time               time                                        null,
            23  => [
                'getAfterPosition' => "field_datetime_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_time',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TIME,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_time_default       time          default '12:34:56'            null,
            24  => [
                'getAfterPosition' => "field_time",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "12:34:56",
                'getName'          => 'field_time_default',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TIME,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_timestamp          timestamp                                   null,
            25  => [
                'getAfterPosition' => "field_time_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_timestamp',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TIMESTAMP,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_timestamp_default  timestamp     default '2018-10-01 12:34:56' null,
            26  => [
                'getAfterPosition' => "field_timestamp",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "2018-10-01 12:34:56",
                'getName'          => 'field_timestamp_default',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TIMESTAMP,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_mediumint          mediumint(10) unsigned                      null,
            27  => [
                'getAfterPosition' => "field_timestamp_default",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_mediumint',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_MEDIUMINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => true,
            ],
            // field_mediumint_default  mediumint(10) default 1                     null,
            28  => [
                'getAfterPosition' => "field_mediumint",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "1",
                'getName'          => 'field_mediumint_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_MEDIUMINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_smallint           smallint(10)  unsigned                      null,
            29  => [
                'getAfterPosition' => "field_mediumint_default",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_smallint',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_SMALLINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => true,
            ],
            // field_smallint_default   smallint(10)  default 1                     null,
            30  => [
                'getAfterPosition' => "field_smallint",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "1",
                'getName'          => 'field_smallint_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_SMALLINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_tinyint            tinyint(10)   unsigned                      null,
            31  => [
                'getAfterPosition' => "field_smallint_default",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_tinyint',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_TINYINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => true,
            ],
            // field_tinyint_default    tinyint(10)   default 1                     null,
            32  => [
                'getAfterPosition' => "field_tinyint",
                'getBindType'      => Column::BIND_PARAM_INT,
                'getComment'       => "",
                'getDefault'       => "1",
                'getName'          => 'field_tinyint_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_TINYINTEGER,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => true,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_longtext           longtext                                    null,
            33  => [
                'getAfterPosition' => "field_tinyint_default",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_longtext',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_LONGTEXT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_mediumtext         mediumtext                                  null,
            34  => [
                'getAfterPosition' => "field_longtext",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_mediumtext',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_MEDIUMTEXT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_tinytext           tinytext                                    null,
            35  => [
                'getAfterPosition' => "field_mediumtext",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_tinytext',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TINYTEXT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_text               text                                        null,
            36  => [
                'getAfterPosition' => "field_tinytext",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_text',
                'getScale'         => 0,
                'getSize'          => 0,
                'getType'          => Column::TYPE_TEXT,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_varbinary          varbinary(10)                               null,
            37  => [
                'getAfterPosition' => "field_text",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_varbinary',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_VARBINARY,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_varchar            varchar(10)                                 null,
            38  => [
                'getAfterPosition' => "field_varbinary",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => null,
                'getName'          => 'field_varchar',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_VARCHAR,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => false,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
            // field_varchar_default    varchar(10) 'D'                             null,
            39  => [
                'getAfterPosition' => "field_varchar",
                'getBindType'      => Column::BIND_PARAM_STR,
                'getComment'       => "",
                'getDefault'       => "D",
                'getName'          => 'field_varchar_default',
                'getScale'         => 0,
                'getSize'          => 10,
                'getType'          => Column::TYPE_VARCHAR,
                'getTypeReference' => -1,
                'getTypeValues'    => [],
                'hasDefault'       => true,
                'isAutoIncrement'  => false,
                'isFirst'          => false,
                'isNotNull'        => false,
                'isNumeric'        => false,
                'isPrimary'        => false,
                'isUnsigned'       => false,
            ],
        ];

        return $metadata[$index] ?? [];
    }
}
