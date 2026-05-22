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

use Phalcon\Mvc\Model\Exception;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\InvoicesMigration;
use Phalcon\Tests\Support\Models\Invoices;
use Phalcon\Tests\Support\Models\InvoicesKeepSnapshots;
use Phalcon\Tests\Support\Traits\DiTrait;

final class GetUpdatedFieldsTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        \Phalcon\Mvc\Model::setup(['updateSnapshotOnSave' => true]);
    }

    public function tearDown(): void
    {
        $this->tearDownDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model :: getUpdatedFields() - not persisted throws
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @group mysql
     * @group pgsql
     * @group sqlite
     */
    public function testMvcModelGetUpdatedFieldsNotPersisted(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Change checking cannot be performed because the object has not been persisted or is deleted"
        );

        (new Invoices())->getUpdatedFields();
    }

    /**
     * Regression coverage for [#CP-17042]: when both the current snapshot and
     * the previous oldSnapshot hold `null` for a nullable column, the
     * column must NOT be reported as updated.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-05-21
     *
     * @group mysql
     * @group pgsql
     * @group sqlite
     */
    public function testMvcModelGetUpdatedFieldsIgnoresUnchangedNullColumns(): void
    {
        $connection = self::getConnection();
        (new InvoicesMigration($connection));

        $stmt = $connection->prepare(
            'INSERT INTO co_invoices (inv_id, inv_cst_id, inv_status_flag, inv_title, inv_total, inv_created_at) '
            . 'VALUES (98, NULL, NULL, :title, NULL, :createdAt)'
        );
        $stmt->execute([
            ':title'     => 'null-cols',
            ':createdAt' => date('Y-m-d H:i:s'),
        ]);

        $invoice = InvoicesKeepSnapshots::findFirst(98);
        $this->assertNotFalse($invoice);

        $invoice->inv_title = 'Updated';
        $this->assertTrue($invoice->save());

        $updated = $invoice->getUpdatedFields();
        $this->assertContains('inv_title', $updated);
        $this->assertNotContains('inv_cst_id', $updated);
        $this->assertNotContains('inv_status_flag', $updated);
        $this->assertNotContains('inv_total', $updated);
    }
}
