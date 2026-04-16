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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Mysql;

use Phalcon\Db\Index;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\DialectMigration;
use Phalcon\Tests\Support\Traits\DiTrait;

use function env;

final class DescribeIndexesTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo\Mysql :: describeIndexes()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  mysql
     */
    public function testDbAdapterPdoMysqlDescribeIndexes(): void
    {
        $db        = $this->container->get('db');
        $migration = new DialectMigration(self::getConnection());
        $table     = $migration->getTable();

        $expected = [
            'PRIMARY'                  => new Index(
                'PRIMARY',
                ['field_primary'],
                'PRIMARY'
            ),
            'dialect_table_unique'     => new Index(
                'dialect_table_unique',
                ['field_integer'],
                'UNIQUE'
            ),
            'dialect_table_index'      => new Index(
                'dialect_table_index',
                ['field_bigint'],
                ''
            ),
            'dialect_table_two_fields' => new Index(
                'dialect_table_two_fields',
                ['field_char', 'field_char_default'],
                ''
            ),
        ];

        $this->assertEquals(
            $expected,
            $db->describeIndexes($table)
        );

        $this->assertEquals(
            $expected,
            $db->describeIndexes($table, env('DATA_MYSQL_NAME'))
        );
    }
}
