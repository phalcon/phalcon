<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Info\Adapter\Mysql;

use Phalcon\DataMapper\Info\Adapter\Mysql;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListTablesTest extends AbstractDatabaseTestCase
{
    /**
     * @since 2025-01-14
     *
     * @group mysql
     */
    public function testDmInfoAdapterMysqlListTables(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $mysql  = new Mysql($connection);
        $schema = $mysql->getCurrentSchema();

        $expected = [
            'album',
            'album_photo',
            'albums',
            'artists',
            'co_customers',
            'co_customers_defaults',
            'co_dialect',
            'co_invoices',
            'co_manufacturers',
            'co_only_identity',
            'co_orders',
            'co_orders_x_products',
            'co_products',
            'co_rb_test_model',
            'co_setters',
            'co_sources',
            'complex_default',
            'fractal_dates',
            'no_primary_key',
            'objects',
            'personas',
            'ph_select',
            'photo',
            'songs',
            'stuff',
            'table_with_uuid_primary',
        ];
        $actual   = $mysql->listTables($schema);

        sort($actual);

        $this->assertSame($expected, $actual);
    }
}
