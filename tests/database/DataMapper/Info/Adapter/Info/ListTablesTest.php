<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Info\Adapter\Info;

use Phalcon\DataMapper\Info\Info;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListTablesTest extends AbstractDatabaseTestCase
{
    /**
     * @since 2025-01-14
     *
     * @group mysql
     */
    public function testDmInfoAdapterInfoListTables(): void
    {
        $connection = self::getDataMapperConnection();
        $info       = Info::new($connection);
        $schema     = $info->getCurrentSchema();

        $expected = [
            'album',
            'album_photo',
            'albums',
            'artists',
            'co_customers',
            'co_customers_defaults',
            'co_customers_fk',
            'co_dialect',
            'co_invoices',
            'co_invoices_fk',
            'co_manufacturers',
            'co_only_identity',
            'co_orders',
            'co_orders_x_products',
            'co_orders_x_products_mult',
            'co_orders_x_products_mult_comp',
            'co_orders_x_products_one',
            'co_orders_x_products_one_comp',
            'co_products',
            'co_rb_test_model',
            'co_setters',
            'co_sources',
            'complex_default',
            'foreign_key_child',
            'foreign_key_parent',
            'fractal_dates',
            'no_primary_key',
            'objects',
            'personas',
            'ph_select',
            'photo',
            'songs',
            'stuff',
            'table_with_string_field',
            'table_with_uuid_primary',
        ];
        $actual   = $info->listTables($schema);

        sort($actual);

        $this->assertSame($expected, $actual);
    }
}
