<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Integration\Mvc\Model;

use DatabaseTester;
use PDO;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Tests\Fixtures\Migrations\CustomersMigration;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

class UnderscoreCallStaticCest
{
    use DiTrait;

    public function _after(DatabaseTester $I)
    {
        $this->container['db']->close();
    }

    public function _before(DatabaseTester $I)
    {
        $this->setNewFactoryDefault();
        $this->setDatabase($I);
    }

    /**
     * Tests Phalcon\Mvc\Model :: __callStatic()
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-10-14
     *
     * @group  mysql
     * @group  pgsql
     * @group  sqlite
     */
    public function mvcModelUnderscoreCallStatic(DatabaseTester $I)
    {
        $I->wantToTest("Mvc\Model - __callStatic()");

        /** @var PDO $connection */
        $connection = $I->getConnection();

        $invoicesMigration = new InvoicesMigration($connection);
        $invoicesMigration->insert(77, 1, 0, uniqid('inv-'));
        $invoicesMigration->insert(88, 1, 1, uniqid('inv-'));

        $customersMigration = new CustomersMigration($connection);
        $customersMigration->insert(1, 1, 'test_firstName_1', 'test_lastName_1');
        $customersMigration->insert(2, 0, 'test_firstName_2', 'test_lastName_2');

        /**
         * Testing Model::findByField()
         */
        $magicInvoices = Invoices::findByInvId(77);

        $I->assertInstanceOf(Resultset\Simple::class, $magicInvoices);
        $I->assertCount(1, $magicInvoices);
        $I->assertInstanceOf(Invoices::class, $magicInvoices->getFirst());

        /**
         * Testing Model::findByField()
         * with impossible conditions
         */
        $nonExistentInvoices = Invoices::findByInvId(0);

        $I->assertInstanceOf(Resultset\Simple::class, $nonExistentInvoices);
        $I->assertCount(0, $nonExistentInvoices);

        /**
         * Testing Model::findFirstByField()
         */
        $firstMagicInvoice = Invoices::findFirstByInvCstId(1);
        $I->assertInstanceOf(Invoices::class, $firstMagicInvoice);

        /**
         * Testing Model::findFirstByField()
         * with impossible conditions
         */
        $nonExistentFirstInvoice = Invoices::findFirstByInvCstId(0);

        $I->assertNull($nonExistentFirstInvoice);

        /**
         * Testing Model::countByField()
         */
        $countMagicInvoices = Invoices::countByInvCstId(1);

        $I->assertIsInt($countMagicInvoices);
        $I->assertEquals(2, $countMagicInvoices);

        /**
         * Testing Model::countByField()
         * with impossible conditions
         */
        $countEmptyMagicInvoices = Invoices::countByInvCstId(null);

        $I->assertIsInt($countEmptyMagicInvoices);
        $I->assertEquals(0, $countEmptyMagicInvoices);

        /**
         * Testing with unknown method
         */
        $I->expectThrowable(
            new Exception(
                "The method 'nonExistentStaticMethod' doesn't exist on model '" . Invoices::class . "'"
            ),
            function () {
                Invoices::nonExistentStaticMethod(1);
            }
        );

        /**
         * Testing Model::findFirstByField() with unknown field
         */
        $I->expectThrowable(
            new Exception(
                "Cannot resolve attribute 'UnknownField' in the model '" . Invoices::class . "'"
            ),
            function () {
                Invoices::findFirstByUnknownField(1);
            }
        );

        /**
         * Testing Model::countByField() with unknown field
         */
        $I->expectThrowable(
            new Exception(
                "Cannot resolve attribute 'UnknownField' in the model '" . Invoices::class . "'"
            ),
            function () {
                Invoices::countByUnknownField(1);
            }
        );
    }
}
