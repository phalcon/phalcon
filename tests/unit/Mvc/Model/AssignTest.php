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
use Phalcon\Mvc\Model\Transaction\Manager;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function uniqid;

final class AssignTest extends DatabaseTestCase
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
     * Tests Phalcon\Mvc\Model :: assign()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-18
     *
     * @group  common
     */
    public function testMvcModelAssign(): void
    {
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $data  = [
            'inv_id'          => 1,
            'inv_cst_id'      => 2,
            'inv_status_flag' => 3,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => $date,
        ];

        $invoice = new Invoices();
        $invoice->assign($data);

        $this->assertEquals(
            1,
            $invoice->readAttribute('inv_id')
        );
        $this->assertEquals(
            2,
            $invoice->readAttribute('inv_cst_id')
        );
        $this->assertEquals(
            3,
            $invoice->readAttribute('inv_status_flag')
        );
        $this->assertEquals(
            $title,
            $invoice->readAttribute('inv_title')
        );
        $this->assertEquals(
            100.12,
            $invoice->readAttribute('inv_total')
        );
        $this->assertEquals(
            $date,
            $invoice->readAttribute('inv_created_at')
        );

        $this->assertEquals(
            $data,
            $invoice->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: assign() - auto_increment primary
     *
     * Current test serves for example with PHP 7.4 and nullable model's
     * property.
     * > Uncaught Error: Typed property Model::$id must not be accessed before
     * initialization
     *
     * Example: public ?int $id = null;
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-13
     *
     * @group  common
     */
    public function testMvcModelAssignAutoPrimary(): void
    {
        $data = [
            'inv_cst_id'      => 2,
            'inv_status_flag' => 3,
            'inv_title'       => uniqid('inv-'),
            'inv_total'       => 100.12,
            'inv_created_at'  => date('Y-m-d H:i:s'),
        ];

        $invoice = new Invoices();
        $invoice->assign($data, array_keys($data));

        $this->assertArrayHasKey('inv_id', $invoice->toArray());
        $this->assertEmpty($invoice->toArray()['inv_id']);
    }

    /**
     * Tests Phalcon\Mvc\Model :: assign() - incomplete
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-29
     *
     * @group  common
     */
    public function testMvcModelAssignIncomplete(): void
    {
        $title   = uniqid('inv-');
        $invoice = new Invoices();
        $invoice->assign(
            [
                'inv_id'    => 1,
                'inv_title' => $title,
            ]
        );

        $this->assertEquals(
            [
                'inv_id'          => 1,
                'inv_cst_id'      => null,
                'inv_status_flag' => null,
                'inv_title'       => $title,
                'inv_total'       => null,
                'inv_created_at'  => null,
            ],
            $invoice->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: assign() - with transaction
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-29
     * @issue  https://github.com/phalcon/cphalcon/issues/15739
     *
     * @group  mysql
     * @group  sqlite
     */
    public function testMvcModelAssignWithTransaction(): void
    {
        $title       = uniqid('inv-');
        $manager     = new Manager();
        $transaction = $manager->get();
        $invoice     = new Invoices();
        $invoice->setTransaction($transaction);
        $invoice->assign(
            [
                'inv_id'    => 1,
                'inv_title' => $title,
            ]
        );

        $result = $invoice->create();
        $this->assertTrue($result);

        $result = $transaction->commit();
        $this->assertTrue($result);

        $result = $invoice->delete();
        $this->assertTrue($result);
    }
}
