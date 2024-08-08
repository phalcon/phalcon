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
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function uniqid;

final class ReadWriteAttributeTest extends DatabaseTestCase
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
     * Tests Phalcon\Mvc\Model :: writeAttribute()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-18
     *
     * @group  common
     */
    public function testMvcModelWriteAttribute(): void
    {
        $title   = uniqid('inv-');
        $invoice = new Invoices();
        $invoice->writeAttribute('inv_id', 123);
        $invoice->writeAttribute('inv_title', $title);

        $this->assertEquals(123, $invoice->readAttribute('inv_id'));
        $this->assertEquals($title, $invoice->readAttribute('inv_title'));

        $this->assertEquals(
            [
                'inv_id'          => 123,
                'inv_title'       => $title,
                'inv_cst_id'      => null,
                'inv_status_flag' => null,
                'inv_total'       => null,
                'inv_created_at'  => null,
            ],
            $invoice->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: writeAttribute() undefined property with
     * associative array
     *
     * @issue  14021
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-04-30
     *
     * @group  common
     */
    public function testMvcModelWriteAttributeUndefinedPropertyWithAssociativeArray(): void
    {
        $array = [
            'inv_id'    => 123,
            'inv_title' => uniqid('inv-'),
        ];

        $invoice = new Invoices();
        @$invoice->writeAttribute('whatEverUndefinedProperty', $array);

        $this->assertEquals(
            [
                'inv_id'          => null,
                'inv_title'       => null,
                'inv_cst_id'      => null,
                'inv_status_flag' => null,
                'inv_total'       => null,
                'inv_created_at'  => null,
            ],
            $invoice->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: writeAttribute() with associative array
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-04-30
     *
     * @group  common
     */
    public function testMvcModelWriteAttributeWithAssociativeArray(): void
    {
        $array = [
            'one' => uniqid('one-'),
            'two' => uniqid('two-'),
        ];

        $invoice = new Invoices();
        $invoice->writeAttribute('inv_id', 123);
        $invoice->writeAttribute('inv_title', $array);

        $this->assertEquals(
            $array,
            $invoice->readAttribute('inv_title')
        );

        $this->assertEquals(
            [
                'inv_id'          => 123,
                'inv_title'       => $array,
                'inv_cst_id'      => null,
                'inv_status_flag' => null,
                'inv_total'       => null,
                'inv_created_at'  => null,
            ],
            $invoice->toArray()
        );
    }
}
