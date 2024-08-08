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

final class ListViewsTest extends DatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                "SELECT `TABLE_NAME` AS view_name "
                . "FROM `INFORMATION_SCHEMA`.`VIEWS` "
                . "WHERE `TABLE_SCHEMA` = 'schema' "
                . "ORDER BY view_name",
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
                "SELECT `TABLE_NAME` AS view_name "
                . "FROM `INFORMATION_SCHEMA`.`VIEWS` "
                . "WHERE `TABLE_SCHEMA` = DATABASE() "
                . "ORDER BY view_name",
            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: listViews
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectListViews(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->listViews('schema');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: listViews
     *
     * @dataProvider getDialectsNoSchema
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectListViewsNoSchema(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->listViews();
        $this->assertSame($expected, $actual);
    }
}
