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
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function date;
use function serialize;
use function uniqid;
use function unserialize;

final class SerializeTest extends AbstractDatabaseTestCase
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
     * Tests Phalcon\Mvc\Model :: serialize()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelSerialize(): void
    {
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $data  = [
            'inv_cst_id'      => 2,
            'inv_status_flag' => 3,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => $date,
        ];

        $invoice = new Invoices();
        $invoice->assign($data);

        $result = $invoice->save();
        $this->assertNotFalse($result);

        $serialized = serialize($invoice);
        $newObject  = unserialize($serialized);

        $this->assertEquals(2, $newObject->inv_cst_id);
        $this->assertEquals(3, $newObject->inv_status_flag);
        $this->assertEquals($title, $newObject->inv_title);
        $this->assertEquals(100.12, $newObject->inv_total);
        $this->assertEquals($date, $newObject->inv_created_at);
    }

    /**
     * Tests Phalcon\Mvc\Model :: serialize() - with dirtyState
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-11-09
     *
     * @group mysql
     */
    public function testMvcModelSerializeWithDirtyState(): void
    {
        $title = uniqid('inv-');
        $date  = date('Y-m-d H:i:s');
        $data  = [
            'inv_cst_id'      => 2,
            'inv_status_flag' => 3,
            'inv_title'       => $title,
            'inv_total'       => 100.12,
            'inv_created_at'  => $date,
        ];

        $invoice = new Invoices();
        $invoice->assign($data);
        $invoice->setDirtyState(0);

        $result = $invoice->save();
        $this->assertNotFalse($result);

        $serialized = serialize($invoice);

        /** @var Invoices $newObject */
        $newObject = unserialize($serialized);
        $this->assertEquals(0, $newObject->getDirtyState());
    }
}
