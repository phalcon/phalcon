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
use Phalcon\Db\Dialect\Postgresql;
use Phalcon\Db\Dialect\Sqlite;
use Phalcon\Tests\DatabaseTestCase;

final class TableExistsTest extends DatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                "SELECT IF(COUNT(*) > 0, 1, 0) "
                . "FROM `INFORMATION_SCHEMA`.`TABLES` "
                . "WHERE `TABLE_NAME` = 'table' "
                . "AND `TABLE_SCHEMA` = 'schema'",
            ],
            [
                Postgresql::class,
                "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
                . "FROM information_schema.tables "
                . "WHERE table_schema = 'public' "
                . "AND table_name='table'",
            ],
            [
                Sqlite::class,
                "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
                . "FROM sqlite_master "
                . "WHERE type='table' "
                . "AND tbl_name='table'",
            ],
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
                "SELECT IF(COUNT(*) > 0, 1, 0) "
                . "FROM `INFORMATION_SCHEMA`.`TABLES` "
                . "WHERE `TABLE_NAME` = 'table' "
                . "AND `TABLE_SCHEMA` = DATABASE()",
            ],
            [
                Postgresql::class,
                "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
                . "FROM information_schema.tables "
                . "WHERE table_schema = '' "
                . "AND table_name='table'",
            ],
            [
                Sqlite::class,
                "SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END "
                . "FROM sqlite_master "
                . "WHERE type='table' "
                . "AND tbl_name='table'",
            ],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: tableExists
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectTableExists(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->tableExists('table', 'schema');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: tableExists
     *
     * @dataProvider getDialectsNoSchema
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectTableExistsNoSchema(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->tableExists('table');
        $this->assertSame($expected, $actual);
    }
}
