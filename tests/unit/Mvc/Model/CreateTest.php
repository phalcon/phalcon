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

namespace Phalcon\Tests\Unit\Mvc\Model;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function date;
use function uniqid;

final class CreateTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        /** @var PDO $connection */
        $connection = $this->getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Mvc\Model :: create()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  mysql
     * @group  sqlite
     * @group  pgsql
     */
    public function testMvcModelCreate(): void
    {
        $title                    = uniqid('inv-');
        $date                     = date('Y-m-d H:i:s');
        $invoice                  = new Invoices();
        $invoice->inv_cst_id      = 2;
        $invoice->inv_status_flag = 3;
        $invoice->inv_title       = $title;
        $invoice->inv_total       = 100.12;
        $invoice->inv_created_at  = $date;

        $result = $invoice->create();
        $this->assertNotFalse($result);
    }
}
