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

use Phalcon\Db\Dialect\Mysql;
use Phalcon\Tests\DatabaseTestCase;

final class DescribeReferencesTest extends DatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                "SELECT DISTINCT "
                . "KCU.TABLE_NAME, KCU.COLUMN_NAME, KCU.CONSTRAINT_NAME, "
                . "KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, "
                . "KCU.REFERENCED_COLUMN_NAME, RC.UPDATE_RULE, "
                . "RC.DELETE_RULE "
                . "FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU "
                . "LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS RC "
                . "ON RC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME "
                . "AND RC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA "
                . "WHERE KCU.REFERENCED_TABLE_NAME IS NOT NULL "
                . "AND KCU.CONSTRAINT_SCHEMA = 'schema' "
                . "AND KCU.TABLE_NAME = 'table'",

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsNoSchema(): array
    {
        return [
            [
                Mysql::class,
                "SELECT DISTINCT "
                . "KCU.TABLE_NAME, KCU.COLUMN_NAME, KCU.CONSTRAINT_NAME, "
                . "KCU.REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, "
                . "KCU.REFERENCED_COLUMN_NAME, RC.UPDATE_RULE, RC.DELETE_RULE "
                . "FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU "
                . "LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS RC "
                . "ON RC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME "
                . "AND RC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA "
                . "WHERE KCU.REFERENCED_TABLE_NAME IS NOT NULL "
                . "AND KCU.CONSTRAINT_SCHEMA = DATABASE() "
                . "AND KCU.TABLE_NAME = 'table'",
            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: describeReferences
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectDescribeReferences(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->describeReferences('table', 'schema');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: describeReferences
     *
     * @dataProvider getDialectsNoSchema
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectDescribeReferencesNoSchema(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->describeReferences('table');
        $this->assertSame($expected, $actual);
    }
}
