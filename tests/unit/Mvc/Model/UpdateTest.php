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
use Phalcon\Mvc\ModelInterface;
use Phalcon\Tests\Fixtures\Migrations\CustomersDefaultsMigration;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Migrations\SettersMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\CustomersDefaults;
use Phalcon\Tests\Models\Invoices;
use Phalcon\Tests\Models\Setters;

use function uniqid;

final class UpdateTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $connection = $this->getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Mvc\Model :: update() - with default values
     *
     * @see    https://github.com/phalcon/cphalcon/issues/14924
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2020-10-18
     *
     * @group  common
     */
    public function testMvcModelSaveAfterWithoutDefaultValues(): void
    {
        /** @var PDO $connection */
        $connection = $this->getConnection();

        $customersMigration = new CustomersDefaultsMigration($connection);
        $customersMigration->clear();

        /**
         * Customer is created manually with empty first and last name
         */
        $customersMigration->insert(1, 1, null, null);

        $manualCustomer = CustomersDefaults::findFirst(1);

        $this->assertEquals(
            '',
            $manualCustomer->cst_name_first
        );

        $this->assertEquals(
            '',
            $manualCustomer->cst_name_last
        );

        /**
         * Validation should fail because we don't allow
         * empty strings for `not null` columns
         */
        $this->assertFalse(
            $manualCustomer->update()
        );

        /**
         * Customer is created by ORM with proper default values
         */
        $ormCustomer = new CustomersDefaults();

        $this->assertTrue(
            $ormCustomer->create()
        );

        $this->assertEquals(
            'cst_default_firstName',
            $ormCustomer->cst_name_first
        );

        $this->assertEquals(
            'cst_default_lastName',
            $ormCustomer->cst_name_last
        );

        $this->assertTrue(
            $ormCustomer->update()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: update() - via setters and local method
     *
     * @see    https://github.com/phalcon/cphalcon/discussions/15625
     *
     * @author Anton Vasiliev <https://github.com/Jeckerson>
     * @since  2021-08-20
     *
     * @group  common
     */
    public function testMvcModelSaveViaSettersAndLocalMethod(): void
    {
        /** @var PDO $connection */
        $connection = $this->getConnection();

        $settersMigration = new SettersMigration($connection);
        $settersMigration->clear();
        $settersMigration->insert('value1', 'value2', 'value3');

        /**
         * Validate initial data
         */
        $row = Setters::findFirst(1);
        $this->assertEquals('value1', $row->getColumn1());
        $this->assertEquals('value2', $row->getColumn2());
        $this->assertEquals('value3', $row->getColumn3());

        /**
         * First save via local method
         */
        $firstValue = 'value2';
        $this->setColumn1($row, $firstValue);
        $this->assertEquals($firstValue, $row->getColumn1());
        $this->assertEquals($firstValue, Setters::findFirst(1)->getColumn1());

        /**
         * Second save via model's setter and direct save() call
         */
        $secondValue = 'value3';
        $row->setColumn2($secondValue);
        $row->save();
        $this->assertEquals($secondValue, $row->getColumn2());
        $this->assertEquals($secondValue, Setters::findFirst(1)->getColumn2());

        /**
         * Final assertions
         */
        $this->assertEquals($firstValue, $row->getColumn1());
        $this->assertEquals($secondValue, $row->getColumn2());
        $this->assertEquals('value3', $row->getColumn3());
        $this->assertEquals($firstValue, Setters::findFirst(1)->getColumn1());
        $this->assertEquals($secondValue, Setters::findFirst(1)->getColumn2());
        $this->assertEquals('value3', Setters::findFirst(1)->getColumn3());
    }

    /**
     * Tests Phalcon\Mvc\Model :: update()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-31
     *
     * @group  mysql
     * @group  sqlite
     * @group  pgsql
     */
    public function testMvcModelUpdate(): void
    {
        $title   = uniqid('inv-');
        $invoice = new Invoices();
        $invoice->assign(
            [
                'inv_title' => $title,
            ]
        );

        $result = $invoice->save();
        $this->assertNotFalse($result);

        $invoice->inv_cst_id      = 456;
        $invoice->inv_status_flag = 2;

        $result = $invoice->update();
        $this->assertNotFalse($result);

        /**
         * Get the record again to ensure that the update is successful
         */
        $record = Invoices::findFirst(
            [
                'conditions' => 'inv_title = :title:',
                'bind'       => [
                    'title' => $title,
                ],
            ]
        );

        $this->assertEquals(
            [
                'inv_id'          => $invoice->inv_id,
                'inv_title'       => $title,
                'inv_cst_id'      => 456,
                'inv_status_flag' => 2,
                'inv_total'       => null,
                'inv_created_at'  => null,
            ],
            $record->toArray()
        );
    }

    private function setColumn1(ModelInterface $model, string $value): void
    {
        $model->setColumn1($value);
        $model->save();
    }
}
