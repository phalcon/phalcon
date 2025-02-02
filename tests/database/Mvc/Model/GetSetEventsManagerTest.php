<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Mvc\Model;

use Exception;
use Phalcon\Events\Manager;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function uniqid;

final class GetSetEventsManagerTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * @var InvoicesMigration
     */
    private InvoicesMigration $invoiceMigration;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        try {
            $this->setNewFactoryDefault();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->setDatabase();

        $this->invoiceMigration = new InvoicesMigration(self::getConnection());
    }

    /**
     * Tests Phalcon\Mvc\Model :: getEventsManager()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelGetEventsManager(): void
    {
        $this->invoiceMigration->insert(4, null, 0, uniqid('inv-', true));

        $invoice = Invoices::findFirst();

        $this->assertNull(
            $invoice->getEventsManager()
        );

        $manager = new Manager();
        $invoice->setEventsManager($manager);

        $this->assertEquals(
            $manager,
            $invoice->getEventsManager()
        );
    }
}
