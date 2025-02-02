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

final class DescribeIndexesTest extends AbstractDatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                'SHOW INDEXES FROM `schema`.`table`',

            ],
            [
                Postgresql::class,
                "SELECT 0 as c0, t.relname as table_name, "
                . "i.relname as key_name, 3 as c3, "
                . "a.attname as column_name "
                . "FROM pg_class t, pg_class i, pg_index ix, pg_attribute a "
                . "WHERE t.oid = ix.indrelid "
                . "AND i.oid = ix.indexrelid "
                . "AND a.attrelid = t.oid "
                . "AND a.attnum = "
                . "ANY(ix.indkey) "
                . "AND t.relkind = 'r' "
                . "AND t.relname = 'table' "
                . "ORDER BY t.relname, i.relname;",
            ],
            [
                Sqlite::class,
                "PRAGMA index_list('table')",
            ],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: describeIndexes
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group mysql
     */
    public function testDbDialectDescribeIndexes(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->describeIndexes('table', 'schema');
        $this->assertSame($expected, $actual);
    }
}
