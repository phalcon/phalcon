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

use Phalcon\Mvc\Model\Resultset\Complex;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\Model\Row;
use Phalcon\Storage\Exception;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\CustomersMigration;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Customers;
use Phalcon\Tests\Models\CustomersKeepSnapshots;
use Phalcon\Tests\Models\InvoicesKeepSnapshots;

use function uniqid;

final class QueryTest extends DatabaseTestCase
{
    use DiTrait;
    use RecordsTrait;

    /**
     * @var CustomersMigration
     */
    private CustomersMigration $customerMigration;

    /**
     * @var InvoicesMigration
     */
    private InvoicesMigration $invoiceMigration;

    public function tearDown(): void
    {
        $this->container['db']->close();
    }

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

        $this->customerMigration = new CustomersMigration(self::getConnection());
        $this->invoiceMigration  = new InvoicesMigration(self::getConnection());
    }

    /**
     * Tests Phalcon\Mvc\Model :: query()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @group  common
     */
    public function testMvcModelQuery(): void
    {
        $this->addTestData();

        $query = Customers::query();
        $query->limit(20, 0);
        $resultsets = $query->execute();

        $this->assertEquals(20, $resultsets->count());
        foreach ($resultsets as $resultset) {
            $this->assertInstanceOf(Customers::class, $resultset);
        }
    }

    /**
     * Tests Phalcon\Mvc\Model :: query() - Issue 14535
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-01
     * @issue  14535
     *
     * @group  common
     */
    public function testMvcModelQueryIssue14535(): void
    {
        $this->addTestData();

        $query = Customers::query();
        $query->columns(
            [
                'Customer ID' => 'cst_id',
                'Stätûs'      => 'cst_status_flag',
            ]
        );
        $query->limit(1, 0);
        $resultsets = $query->execute();

        $this->assertTrue(isset($resultsets[0]['Customer ID']));
        $this->assertTrue(isset($resultsets[0]['Stätûs']));
    }

    /**
     * Tests Phalcon\Mvc\Model :: query() - Issue 14783
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @issue  14783
     *
     * @group  common
     */
    public function testMvcModelQueryIssue14783(): void
    {
        $this->addTestData();

        $query = CustomersKeepSnapshots::query();
        $query->columns(
            [
                CustomersKeepSnapshots::class . '.*',
                'join_1.*',
            ]
        );

        $query->leftJoin(
            InvoicesKeepSnapshots::class,
            'join_1.inv_cst_id = ' . CustomersKeepSnapshots::class . '.cst_id',
            'join_1'
        );

        $query->limit(20, 0);

        /** @var Complex $resultsets */
        $resultsets = $query->execute();

        $this->assertEquals(20, $resultsets->count());
        foreach ($resultsets as $resultset) {
            /** @var Row $resultset */
            $model = $this->transform($resultset);
            $this->assertInstanceOf(CustomersKeepSnapshots::class, $model);
            $this->assertInstanceOf(Simple::class, $model->invoices);
        }
    }

    /**
     * Seed Invoices' table by some data.
     *
     * @return void
     */
    private function addTestData(): void
    {
        for ($counter = 1; $counter <= 50; $counter++) {
            $firstName = uniqid('inv-', true);
            $lastName  = uniqid('inv-', true);

            $this->customerMigration->insert($counter, 1, $firstName, $lastName);
            $this->invoiceMigration->insert($counter, $counter, 1, $firstName);
        }
    }

    /**
     * Transforming method used for test
     *
     * @param Row $row
     *
     * @issue 14783
     *
     * @return mixed
     */
    private function transform(Row $row): CustomersKeepSnapshots
    {
        $invoice           = $row->readAttribute(lcfirst(CustomersKeepSnapshots::class));
        $customer          = $row->readAttribute('join_1');
        $invoice->customer = $customer;

        return $invoice;
    }
}
