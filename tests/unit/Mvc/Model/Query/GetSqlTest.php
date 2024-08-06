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

namespace Phalcon\Tests\Unit\Mvc\Model\Query;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\Query;
use Phalcon\Storage\Exception;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Invoices;

final class GetSqlTest extends DatabaseTestCase
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
     * Tests Phalcon\Mvc\Model\Query :: getSql() - Issue 14657
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-06
     * @issue  14657
     *
     * @group  common
     */
    public function testMvcModelQueryGetSql(): void
    {
        $phql = sprintf('SELECT i.inv_id, i.inv_cst_id FROM [%s] AS i', Invoices::class);

        $query = new Query($phql, $this->container);

        if ('mysql' === $this->invoiceMigration->getDriverName()) {
            $sql = sprintf(
                'SELECT `i`.`inv_id` AS `inv_id`, `i`.`inv_cst_id` AS `inv_cst_id` FROM `%s` AS `i`',
                $this->invoiceMigration->getTable()
            );
        } else {
            $sql = sprintf(
                'SELECT "i"."inv_id" AS "inv_id", "i"."inv_cst_id" AS "inv_cst_id" FROM "%s" AS "i"',
                $this->invoiceMigration->getTable()
            );
        }

        $this->assertEquals(
            [
                'sql'       => $sql,
                'bind'      => [],
                'bindTypes' => [],
            ],
            $query->getSql()
        );
    }
}
