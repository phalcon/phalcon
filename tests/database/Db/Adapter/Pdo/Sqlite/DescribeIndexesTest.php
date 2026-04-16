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

use Phalcon\Db\Index;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

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
     * Tests Phalcon\Db\Adapter\Pdo\Sqlite :: describeIndexes()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  sqlite
     */
    public function testDbAdapterPdoSqliteDescribeIndexes(): void
    {
        $db = $this->container->get('db');

        $expected = [
            'co_invoices_inv_created_at_index'  => new Index(
                'co_invoices_inv_created_at_index',
                ['inv_created_at']
            ),
            'co_invoices_inv_status_flag_index' => new Index(
                'co_invoices_inv_status_flag_index',
                ['inv_status_flag']
            ),
            'co_invoices_inv_cst_id_index'      => new Index(
                'co_invoices_inv_cst_id_index',
                ['inv_cst_id']
            ),
        ];

        $this->assertEquals(
            $expected,
            $db->describeIndexes('co_invoices')
        );

        $this->assertEquals(
            $expected,
            $db->describeIndexes('co_invoices', 'main')
        );
    }
}
