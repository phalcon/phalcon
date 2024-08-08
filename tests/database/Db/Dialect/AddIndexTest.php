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

final class AddIndexTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: addIndex
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddIndex(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $index = new Index('index1', ['field1', 'field2']);
        $actual  = $dialect->addIndex('table', 'schema', $index);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addIndex
     *
     * @dataProvider getDialectsType
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddIndexType(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $index = new Index('index1', ['field1', 'field2'], 'UNIQUE');
        $actual  = $dialect->addIndex('table', 'schema', $index);
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
                . 'ADD INDEX `index1` (`field1`, `field2`)'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsType(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` '
                . 'ADD UNIQUE INDEX `index1` (`field1`, `field2`)'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }
}
