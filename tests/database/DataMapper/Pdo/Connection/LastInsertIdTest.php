<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;

use function date;
use function str_replace;
use function uniqid;

final class LastInsertIdTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: lastInsertId()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLastInsertId(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->clear();

        $template = 'insert into co_invoices (inv_id, inv_cst_id, inv_status_flag, '
            . 'inv_title, inv_total, inv_created_at) values ('
            . "%id%, 1, 1, '%title%', %total%, '%now%')";

        $sql = str_replace(
            [
                '%id%',
                '%title%',
                '%total%',
                '%now%',
            ],
            [
                2,
                uniqid(),
                102,
                date('Y-m-d H:i:s'),
            ],
            $template
        );

        $result = $connection->exec($sql);
        $this->assertSame(1, $result);
        $this->assertSame(2, (int)$connection->lastInsertId());
    }
}
