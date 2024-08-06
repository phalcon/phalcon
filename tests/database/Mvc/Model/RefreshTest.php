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

namespace Phalcon\Tests\Database\Mvc\Model;

use PDO;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function uniqid;

final class RefreshTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        /** @var PDO $connection */
        $connection = self::getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Mvc\Model :: refresh()
     *
     * @group common
     */
    public function testMvcModelRefresh(): void
    {
        $title = uniqid('inv-');
        /** @var PDO $connection */
        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->insert(4, null, 0, $title);

        $invoice = Invoices::findFirst();
        $data    = $invoice->toArray();

        $invoice->assign(
            [
                'inv_id'          => 4,
                'inv_cst_id'      => 5,
                'inv_status_flag' => 6,
                'inv_total'       => 200.12,
            ]
        );

        $invoice->refresh();

        $this->assertEquals(
            $data,
            $invoice->toArray()
        );
    }
}
