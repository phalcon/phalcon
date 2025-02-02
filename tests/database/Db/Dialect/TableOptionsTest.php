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

final class TableOptionsTest extends AbstractDatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                "SELECT TABLES.TABLE_TYPE AS table_type,"
                . "TABLES.AUTO_INCREMENT AS auto_increment,"
                . "TABLES.ENGINE AS engine,"
                . "TABLES.TABLE_COLLATION AS table_collation "
                . "FROM INFORMATION_SCHEMA.TABLES WHERE "
                . "TABLES.TABLE_SCHEMA = 'schema' "
                . "AND TABLES.TABLE_NAME = 'table'",
            ],
            [
                Postgresql::class,
                '',
            ],
            [
                Sqlite::class,
                '',
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
                "SELECT TABLES.TABLE_TYPE AS table_type,"
                . "TABLES.AUTO_INCREMENT AS auto_increment,"
                . "TABLES.ENGINE AS engine,"
                . "TABLES.TABLE_COLLATION AS table_collation "
                . "FROM INFORMATION_SCHEMA.TABLES WHERE "
                . "TABLES.TABLE_SCHEMA = DATABASE() "
                . "AND TABLES.TABLE_NAME = 'table'",
            ],
            [
                Postgresql::class,
                '',
            ],
            [
                Sqlite::class,
                '',
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
     * @group mysql
     */
    public function testDbDialectTableOptions(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->tableOptions('table', 'schema');
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
     * @group mysql
     */
    public function testDbDialectTableOptionsNoSchema(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->tableOptions('table');
        $this->assertSame($expected, $actual);
    }
}
