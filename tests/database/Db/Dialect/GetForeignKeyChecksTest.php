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

final class GetForeignKeyChecksTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: getForeignKeyChecks
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectGetForeignKeyChecks(
        string $dialectClass
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $expected = 'SELECT @@foreign_key_checks';
        $actual  = $dialect->getForeignKeyChecks();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    public static function getDialects(): array
    {
        return [
            [
                Mysql::class

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }
}
