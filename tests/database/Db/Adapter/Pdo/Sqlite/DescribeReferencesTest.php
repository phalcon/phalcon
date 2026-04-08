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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Sqlite;

use Phalcon\Db\Reference;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

final class DescribeReferencesTest extends AbstractDatabaseTestCase
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
     * Tests Phalcon\Db\Adapter\Pdo\Sqlite :: describeReferences()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  sqlite
     */
    public function testDbAdapterPdoSqliteDescribeReferences(): void
    {
        $this->markTestSkipped('Need implementation - no FK-constrained tables in current SQLite schema');

        $db = $this->container->get('db');

        $expected = [
            'foreign_key_0' => new Reference(
                'foreign_key_0',
                [
                    'referencedTable'   => 'parts',
                    'columns'           => ['parts_id'],
                    'referencedColumns' => ['id'],
                    'referencedSchema'  => null,
                ]
            ),
            'foreign_key_1' => new Reference(
                'foreign_key_1',
                [
                    'referencedTable'   => 'robots',
                    'columns'           => ['robots_id'],
                    'referencedColumns' => ['id'],
                    'referencedSchema'  => null,
                ]
            ),
        ];

        $this->assertEquals(
            $expected,
            $db->describeReferences('robots_parts')
        );
    }
}
