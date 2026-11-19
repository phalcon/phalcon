<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Adapter\Pdo;

use PDOException;
use Phalcon\Db\Enum;
use Phalcon\Db\Exceptions\InvalidWhereConditions;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\InvoicesMigration;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

final class UpdateAsDictTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function emptyWhereProvider(): array
    {
        return [
            'empty string' => [''],
            'empty array'  => [[]],
            'zero string'  => ['0'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function incompleteWhereProvider(): array
    {
        return [
            'empty string' => [''],
            'empty array'  => [[]],
        ];
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: updateAsDict()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-05-18
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoUpdateAsDict(): void
    {
        $connection = self::getPdoConnection();
        $db         = $this->container->get('db');

        $migration = new InvoicesMigration($connection);
        $migration->insert(1, 1, 1, 'title 1', 100);
        $migration->insert(2, 1, 1, 'title 2', 200);
        $migration->insert(3, 1, 1, 'title 3', 300);

        $success = $db->updateAsDict(
            'co_invoices',
            [
                'inv_title'       => 'updated dict',
                'inv_status_flag' => 7,
            ],
            'inv_id = 2'
        );
        $this->assertTrue($success);
        $this->assertSame(1, $db->affectedRows());

        $row = $db->fetchOne(
            'SELECT inv_title, inv_status_flag FROM co_invoices WHERE inv_id = ?',
            Enum::FETCH_ASSOC,
            [2]
        );
        $this->assertSame('updated dict', $row['inv_title']);
        $this->assertSame(7, (int) $row['inv_status_flag']);
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: updateAsDict() - an empty where
     * condition does not change all the rows
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/831
     */
    #[DataProvider('emptyWhereProvider')]
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoUpdateAsDictEmptyWhereChangesNoRow(
        mixed $whereCondition
    ): void {
        $connection = self::getPdoConnection();
        $db         = $this->container->get('db');

        $migration = new InvoicesMigration($connection);
        $migration->insert(1, 1, 1, 'title 1', 100);
        $migration->insert(2, 1, 1, 'title 2', 200);
        $migration->insert(3, 1, 1, 'title 3', 300);

        try {
            $db->updateAsDict(
                'co_invoices',
                ['inv_title' => 'changed'],
                $whereCondition
            );
        } catch (PDOException) {
            /**
             * The SQL is not valid for this database. No row changes.
             */
        }

        $actual = $db->fetchColumn(
            "SELECT COUNT(*) FROM co_invoices WHERE inv_title = 'changed'"
        );
        $this->assertSame(0, (int) $actual);
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: updateAsDict() - an empty string or an
     * empty array gives an incomplete WHERE clause
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/831
     */
    #[DataProvider('incompleteWhereProvider')]
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoUpdateAsDictIncompleteWhereThrows(
        mixed $whereCondition
    ): void {
        $db = $this->container->get('db');

        $this->expectException(PDOException::class);

        $db->updateAsDict(
            'co_invoices',
            ['inv_title' => 'changed'],
            $whereCondition
        );
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: updateAsDict() - a where condition that
     * is not a string or an array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/831
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoUpdateAsDictInvalidWhereConditions(): void
    {
        $db = $this->container->get('db');

        $this->expectException(InvalidWhereConditions::class);
        $this->expectExceptionMessage('Invalid WHERE clause conditions');

        $db->updateAsDict(
            'co_invoices',
            ['inv_title' => 'changed'],
            5
        );
    }
}
