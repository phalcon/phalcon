<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Profiler\MemoryLogger;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

final class ExecTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: exec()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionExec(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $this->assertSame(1, $result);
        $result = $migration->insert(2);
        $this->assertSame(1, $result);
        $result = $migration->insert(3);
        $this->assertSame(1, $result);
        $result = $migration->insert(4);
        $this->assertSame(1, $result);

        $all = $connection->exec(
            'update co_invoices set inv_total = inv_total + 100'
        );

        $this->assertSame(4, $all);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: exec() with profiler
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionExecWithProfiler(): void
    {
        $logger   = new MemoryLogger();
        $profiler = new Profiler($logger);
        $profiler->setActive(true);
        $connection = new Connection(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword(),
            [],
            [],
            $profiler
        );

        $migration = new InvoicesMigration($connection);
        $migration->clear();

        $result = $migration->insert(1);
        $this->assertSame(1, $result);
        $result = $migration->insert(2);
        $this->assertSame(1, $result);
        $result = $migration->insert(3);
        $this->assertSame(1, $result);
        $result = $migration->insert(4);
        $this->assertSame(1, $result);

        $all = $connection->exec(
            'update co_invoices set inv_total = inv_total + 100'
        );

        $this->assertSame(4, $all);

        $messages = $logger->getMessages();

        $expected = 10;
        $actual   = $messages;
        $this->assertCount($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection::connect';
        $actual   = $messages[0];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: getAttribute';
        $actual   = $messages[1];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[2];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: delete from co_invoices;';
        $actual   = $messages[2];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: getAttribute';
        $actual   = $messages[3];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[4];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: delete from co_invoices;';
        $actual   = $messages[4];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[5];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: insert into co_invoices ('
            . PHP_EOL
            . '    inv_id, inv_cst_id, inv_status_flag, inv_title, inv_total, inv_created_at'
            . PHP_EOL
            . ') values (';
        $actual   = $messages[5];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[6];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: insert into co_invoices ('
            . PHP_EOL
            . '    inv_id, inv_cst_id, inv_status_flag, inv_title, inv_total, inv_created_at'
            . PHP_EOL
            . ') values (';
        $actual   = $messages[6];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[7];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: insert into co_invoices ('
            . PHP_EOL
            . '    inv_id, inv_cst_id, inv_status_flag, inv_title, inv_total, inv_created_at'
            . PHP_EOL
            . ') values (';
        $actual   = $messages[7];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[8];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: insert into co_invoices ('
            . PHP_EOL
            . '    inv_id, inv_cst_id, inv_status_flag, inv_title, inv_total, inv_created_at'
            . PHP_EOL
            . ') values (';
        $actual   = $messages[8];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'M: Phalcon\DataMapper\Pdo\Connection\AbstractConnection::exec';
        $actual   = $messages[9];
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: update co_invoices set inv_total = inv_total + 100';
        $actual   = $messages[9];
        $this->assertStringContainsString($expected, $actual);
    }
}
