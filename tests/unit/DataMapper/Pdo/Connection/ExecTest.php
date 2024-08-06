<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

final class ExecTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: exec()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionExec(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $this->assertEquals(1, $result);
        $result = $migration->insert(2);
        $this->assertEquals(1, $result);
        $result = $migration->insert(3);
        $this->assertEquals(1, $result);
        $result = $migration->insert(4);
        $this->assertEquals(1, $result);

        $all = $connection->exec(
            'update co_invoices set inv_total = inv_total + 100'
        );

        $this->assertEquals(4, $all);
    }
}
