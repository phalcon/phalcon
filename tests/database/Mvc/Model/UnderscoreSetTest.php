<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Database\Mvc\Model;

use PDO;
use Phalcon\Mvc\Model;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\CustomersMigration;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models;

final class UnderscoreSetTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    public function tearDown(): void
    {
        $this->container['db']->close();
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set()
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSet(): void
    {
        $customer = new Models\Customers();

        $customer->cst_id = 999;

        $this->assertEquals(
            999,
            $customer->cst_id
        );

        $customer->cst_name_first = 'cst_firstName';

        $this->assertEquals(
            'cst_firstName',
            $customer->cst_name_first
        );

        $associativeArray = [
            'value_0',
            'key_1' => 'value_1',
            'key_2' => 'value_2',
        ];

        $customer->cst_data = $associativeArray;

        $this->assertEquals(
            $associativeArray,
            $customer->cst_data
        );

        $this->assertEquals(
            [
                'cst_id'          => 999,
                'cst_status_flag' => null,
                'cst_name_last'   => null,
                'cst_name_first'  => 'cst_firstName',
            ],
            $customer->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() whether it is using setters correctly
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetIsUsingSetters(): void
    {
        $customer = new Models\Customers();

        $customer->id = 999;

        $this->assertEquals(
            999,
            $customer->cst_id
        );

        $customer->firstName = 'cst_firstName';

        $this->assertEquals(
            'cst_firstName',
            $customer->cst_name_first
        );

        $associativeArray = [
            'value_0',
            'key_1' => 'value_1',
            'key_2' => 'value_2',
        ];

        $customer->data = $associativeArray;

        $this->assertEquals(
            $associativeArray,
            $customer->cst_data
        );

        $this->assertEquals(
            [
                'cst_id'          => 999,
                'cst_status_flag' => null,
                'cst_name_last'   => null,
                'cst_name_first'  => 'cst_firstName',
            ],
            $customer->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() undefined property with associative
     * array
     *
     * @see    https://github.com/phalcon/cphalcon/issues/14021
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetUndefinedPropertyWithAssociativeArray(): void
    {
        $associativeArray = [
            'cst_id'         => 123,
            'cst_name_first' => 'cst_firstName',
        ];

        $customer                            = new Models\Customers();
        $customer->whatEverUndefinedProperty = $associativeArray;

        $this->assertEquals(
            [
                'cst_id'          => null,
                'cst_status_flag' => null,
                'cst_name_last'   => null,
                'cst_name_first'  => null,
            ],
            $customer->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with an array as properties of a
     * belongs-to related record
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithArrayOfBelongsToRelatedRecord(): void
    {
        $invoice           = new Models\Invoices();
        $invoice->customer = [
            'cst_id'          => 33,
            'cst_status_flag' => 1,
        ];

        $customer = $invoice->customer;

        $this->assertInstanceOf(
            Models\Customers::class,
            $customer
        );

        $this->assertEquals(
            $invoice->getDirtyState(),
            Model::DIRTY_STATE_TRANSIENT
        );

        $this->assertEquals(
            33,
            $customer->cst_id
        );

        $this->assertEquals(
            1,
            $customer->cst_status_flag
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with an array as properties of a
     * has-one related record
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithArrayOfHasOneRelatedRecord(): void
    {
        $invoice           = new Models\Invoices();
        $invoice->customer = [
            'cst_id'         => 99,
            'cst_name_first' => 'cst_firstName',
        ];

        $customer = $invoice->customer;

        $this->assertInstanceOf(
            Models\Customers::class,
            $customer
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $invoice->getDirtyState()
        );

        $this->assertEquals(
            99,
            $customer->cst_id
        );

        $this->assertEquals(
            'cst_firstName',
            $customer->cst_name_first
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with belongs-to related record
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithBelongsToRelatedRecord(): void
    {
        $customerSnap           = new Models\CustomersKeepSnapshots();
        $customerSnap->invoices = new Models\Invoices();

        $invoices = $customerSnap->invoices;

        $this->assertInstanceOf(
            Models\Invoices::class,
            $invoices
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $customerSnap->getDirtyState()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with has-many related records
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithHasManyRelatedRecords(): void
    {
        /** @var PDO $connection */
        $connection = self::getConnection();

        $invoicesMigration = new InvoicesMigration($connection);
        $invoicesMigration->clear();

        $customersMigration = new CustomersMigration($connection);
        $customersMigration->clear();

        $customer           = new Models\Customers();
        $customer->invoices = [
            new Models\Invoices(),
            new Models\Invoices(),
        ];

        $invoices = $customer->invoices;

        $this->assertIsArray(
            $invoices
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $customer->getDirtyState()
        );

        $this->assertCount(
            2,
            $invoices
        );

        $this->assertInstanceOf(
            Models\Invoices::class,
            $invoices[0]
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $customer->getDirtyState()
        );

        $this->assertTrue(
            $customer->save()
        );

        $customer = Models\Customers::findFirst();

        $this->assertTrue(
            $customer->save()
        );

        /*
         * @see https://github.com/phalcon/cphalcon/issues/13938
         */
        $customer = Models\Customers::findFirst();

        $customer->invoices->delete();

        $this->assertCount(
            0,
            $customer->getRelated('camelCaseInvoices')
        );

        $customer->invoices = [
            new Models\Invoices(),
            new Models\Invoices(),
        ];

        $this->assertTrue($customer->save());

        $this->assertCount(
            2,
            $customer->camelCaseInvoices
        );

        $customer->camelCaseInvoices->delete();

        $this->assertCount(
            0,
            $customer->getRelated('camelCaseInvoices')
        );

        $customer->camelCaseInvoices = [
            new Models\Invoices(),
            new Models\Invoices(),
        ];

        $this->assertTrue(
            $customer->save()
        );

        $this->assertCount(
            2,
            $customer->getRelated('camelCaseInvoices')
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with has-many-to-many related records
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithHasManyToManyRelatedRecords(): void
    {
        $order           = new Models\Orders();
        $order->products = [
            new Models\Products(),
            new Models\Products(),
        ];

        $products = $order->products;

        $this->assertIsArray(
            $products
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $order->getDirtyState()
        );

        $this->assertCount(
            2,
            $products
        );

        $this->assertInstanceOf(
            Models\Products::class,
            $products[0]
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $order->getDirtyState()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with has-one related record
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-02
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithHasOneRelatedRecord(): void
    {
        $invoice           = new Models\Invoices();
        $invoice->customer = new Models\Customers();

        $customer = $invoice->customer;

        $this->assertInstanceOf(
            Models\Customers::class,
            $customer
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $invoice->getDirtyState()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __set() with has-one-through related record
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-11-03
     *
     * @group  common
     */
    public function testMvcModelUnderscoreSetWithHasOneThroughRelatedRecord(): void
    {
        $product = new Models\Products();

        $order                = new Models\Orders();
        $order->singleProduct = $product;

        $this->assertInstanceOf(
            Models\Products::class,
            $order->singleProduct
        );

        $this->assertEquals(
            Model::DIRTY_STATE_TRANSIENT,
            $order->getDirtyState()
        );
    }
}
