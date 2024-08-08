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

final class DropPrimaryKeyTest extends DatabaseTestCase
{
    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` '
                . 'DROP PRIMARY KEY',

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * Tests Phalcon\Db\Dialect :: dropPrimaryKey
     *
     * @dataProvider getDialects
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group        common
     */
    public function testDbDialectDropPrimaryKey(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $actual = $dialect->dropPrimaryKey('table', 'schema');
        $this->assertSame($expected, $actual);
    }
}
