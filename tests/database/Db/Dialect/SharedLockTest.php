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

final class SharedLockTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: sharedLock
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectListViews(
        string $dialectClass
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $expected = 'SQL-QUERY LOCK IN SHARE MODE';
        $actual  = $dialect->sharedLock('SQL-QUERY');
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
            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }
}