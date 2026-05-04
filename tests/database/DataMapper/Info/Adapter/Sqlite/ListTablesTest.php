<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Info\Adapter\Sqlite;

use Phalcon\DataMapper\Info\Adapter\Sqlite;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListTablesTest extends AbstractDatabaseTestCase
{
    /**
     * @since  2025-01-14
     *
     * @group  sqlite
     */
    public function testDmInfoAdapterSqliteListTables(): void
    {
        $connection = self::getDataMapperConnection();

        $sqlite = new Sqlite($connection);
        $schema = $sqlite->getCurrentSchema();

        $expected = [
            'albums',
            'artists',
            'co_customers',
            'co_customers_defaults',
            'co_dialect',
            'co_invoices',
            'co_manufacturers',
            'co_only_identity',
            'co_setters',
            'co_sources',
            'no_primary_key',
            'objects',
            'personas',
            'ph_select',
            'songs',
            'sqlite_sequence',
            'stuff',
            'table_with_uuid_primary',
        ];
        $actual   = $sqlite->listTables($schema);

        sort($actual);

        $this->assertSame($expected, $actual);
    }
}
