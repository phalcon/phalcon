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
use Phalcon\Db\Index;
use Phalcon\Tests\DatabaseTestCase;

final class AddPrimaryKeyTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: addPrimaryKey
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddPrimaryKey(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $index = new Index('index1', ['field1', 'field2']);
        $actual  = $dialect->addPrimaryKey('table', 'schema', $index);
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
                'ALTER TABLE `schema`.`table` '
                . 'ADD PRIMARY KEY (`field1`, `field2`)'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }
}
