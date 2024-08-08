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
use Phalcon\Db\Reference;
use Phalcon\Tests\DatabaseTestCase;

final class AddForeignKeyTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: addForeignKey
     *
     * @dataProvider getDialects
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddForeignKey(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $reference = new Reference('fk1', [
            'referencedSchema'  => 'ref_schema',
            'referencedTable'   => 'ref_table',
            'columns'           => ['field_primary'],
            'referencedColumns' => ['field_referenced'],
        ]);
        $actual  = $dialect->addForeignKey('table', 'schema', $reference);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addForeignKey
     *
     * @dataProvider getDialectsOnDelete
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddForeignKeyOnDelete(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $reference = new Reference('fk1', [
            'referencedSchema'  => 'ref_schema',
            'referencedTable'   => 'ref_table',
            'columns'           => ['field_primary'],
            'referencedColumns' => ['field_referenced'],
            'onDelete'          => 'delete command',
        ]);
        $actual  = $dialect->addForeignKey('table', 'schema', $reference);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Db\Dialect :: addForeignKey
     *
     * @dataProvider getDialectsOnUpdate
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDbDialectAddForeignKeyOnUpdate(
        string $dialectClass,
        string $expected
    ): void {
        /** @var Mysql $dialect */
        $dialect = new $dialectClass();

        $reference = new Reference('fk1', [
            'referencedSchema'  => 'ref_schema',
            'referencedTable'   => 'ref_table',
            'columns'           => ['field_primary'],
            'referencedColumns' => ['field_referenced'],
            'onUpdate'          => 'update command',
        ]);
        $actual  = $dialect->addForeignKey('table', 'schema', $reference);
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
                . 'ADD CONSTRAINT `fk1` FOREIGN KEY (`field_primary`) '
                . 'REFERENCES `ref_schema`.`ref_table`(`field_referenced`)'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsOnDelete(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` '
                . 'ADD CONSTRAINT `fk1` FOREIGN KEY (`field_primary`) '
                . 'REFERENCES `ref_schema`.`ref_table`(`field_referenced`) '
                . 'ON DELETE delete command'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDialectsOnUpdate(): array
    {
        return [
            [
                Mysql::class,
                'ALTER TABLE `schema`.`table` '
                . 'ADD CONSTRAINT `fk1` FOREIGN KEY (`field_primary`) '
                . 'REFERENCES `ref_schema`.`ref_table`(`field_referenced`) '
                . 'ON UPDATE update command'

            ],
            //            [Postgresql::class],
            //            [Sqlite::class],
        ];
    }
}
