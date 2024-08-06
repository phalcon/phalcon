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
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function date;
use function uniqid;

final class UnserializeTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        (new InvoicesMigration($this->getConnection()));
    }

    /**
     * Tests Phalcon\Mvc\Model :: unserialize()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-31
     *
     * @group  common
     */
    public function testMvcModelUnserialize(): void
    {
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $data  = [
            'inv_id'          => null,
            'inv_cst_id'      => 5,
            'inv_status_flag' => 2,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => $date,
        ];

        $invoice = new Invoices();
        $invoice->assign($data);

        $result = $invoice->save();
        $this->assertNotFalse($result);

        $serialized = serialize($data);

        $newModel = new Invoices();
        $newModel->unserialize($serialized);

        $this->assertEquals(
            $data,
            $newModel->toArray()
        );
    }
}
