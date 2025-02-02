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
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListTablesTest extends AbstractDatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                'SHOW TABLES FROM `schema`',

            ],
            [
                Postgresql::class,
                "SELECT table_name "
                . "FROM information_schema.tables "
                . "WHERE table_schema = 'schema' "
                . "ORDER BY table_name",
            ],
            [
                Sqlite::class,
                "SELECT tbl_name "
                . "FROM sqlite_master "
                . "WHERE type = 'table' "
                . "ORDER BY tbl_name",
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
                'SHOW TABLES',

            ],
            [
                Postgresql::class,
                "SELECT table_name "
                . "FROM information_schema.tables "
                . "WHERE table_schema = 'public' "
                . "ORDER BY table_name",
            ],
            [
                Sqlite::class,
                "SELECT tbl_name "
                . "FROM sqlite_master "
                . "WHERE type = 'table' "
                . "ORDER BY tbl_name",
            ],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: listTables
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group mysql
     */
    public function testDbDialectListTables(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->listTables('schema');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: listTables
     *
     * @dataProvider getDialectsNoSchema
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group mysql
     */
    public function testDbDialectListTablesNoSchema(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->listTables();
        $this->assertSame($expected, $actual);
    }
}
