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

namespace Phalcon\Tests\Database\Mvc\Model\Resultset;

use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Row;
use Phalcon\Storage\Exception;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Invoices;

final class GetFirstTest extends AbstractDatabaseTestCase
{
    use DiTrait;
    use RecordsTrait;

    /**
     * @var InvoicesMigration
     */
    private $invoiceMigration;

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
     * Tests Mvc\Model\Resultset :: getFirst() - Issue 15027
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-06
     * @issue  15027
     *
     * @group mysql
     * @group pgsql
     */
    public function testMvcModelResultsetGetFirst(): void
    {
        /**
         * @todo The following tests are skipped for sqlite because we will get
         *       a General Error 5 database is locked error.
         */
        $invId = ('sqlite' === self::getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($this->invoiceMigration, 7, $invId, 2, 'ccc');

        /** @var ManagerInterface $manager */
        $manager = $this->getService('modelsManager');

        $sql = sprintf('SELECT i.inv_id FROM [%s] AS i', Invoices::class);
        $this->assertInstanceOf(Row::class, $manager->createQuery($sql)->execute()->getFirst());

        $sql = sprintf('SELECT * FROM [%s] AS i', Invoices::class);
        $this->assertInstanceOf(Invoices::class, $manager->createQuery($sql)->execute()->getFirst());

        $sql = sprintf('SELECT i.inv_id FROM [%s] AS i WHERE inv_total = -42', Invoices::class);
        $this->assertNull($manager->createQuery($sql)->execute()->getFirst());
    }
}
