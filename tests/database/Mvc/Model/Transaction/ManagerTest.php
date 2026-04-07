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

namespace Phalcon\Tests\Database\Mvc\Model\Transaction;

use Phalcon\Mvc\Model\Transaction\Failed;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\PersonasMigration;
use Phalcon\Tests\Support\Migrations\SelectMigration;
use Phalcon\Tests\Support\Models\Personas;
use Phalcon\Tests\Support\Models\Select;
use Phalcon\Tests\Support\Traits\DiTrait;

final class ManagerTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $connection = self::getConnection();
        new PersonasMigration($connection);
        new SelectMigration($connection);
    }

    public function tearDown(): void
    {
        $this->tearDownDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model\Transaction\Manager :: commit with new inserts
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-08-07
     *
     * @group mysql
     * @group pgsql
     */
    public function testMvcModelTransactionManagerCommitNewInserts(): void
    {
        $tm  = $this->container->getShared('transactionManager');
        $db  = $this->container->getShared('db');

        $db->delete('personas', "cedula LIKE 'T-Cx%'");

        $numPersonas = Personas::count();
        $transaction = $tm->get();

        for ($i = 0; $i < 10; $i++) {
            $persona = new Personas();

            $persona->setDI($this->container);
            $persona->setTransaction($transaction);

            $persona->cedula            = 'T-Cx' . $i;
            $persona->tipo_documento_id = 1;
            $persona->nombres           = 'LOST LOST';
            $persona->telefono          = '2';
            $persona->cupo              = 0;
            $persona->estado            = 'A';

            $this->assertNotFalse(
                $persona->save()
            );
        }

        $this->assertTrue(
            $transaction->commit()
        );

        $expected = $numPersonas + 10;
        $actual   = Personas::count();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Model\Transaction\Manager :: transaction removed on commit
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-08-07
     *
     * @group mysql
     * @group pgsql
     */
    public function testMvcModelTransactionManagerTransactionRemovedOnCommit(): void
    {
        $tm = $this->container->getShared('transactionManager');

        $transaction = $tm->get();

        $select = new Select();

        $select->setTransaction($transaction);

        $select->assign(
            [
                'sel_name' => 'Crack of Dawn',
            ]
        );

        $select->create();

        $this->assertSame(
            1,
            $this->getProtectedProperty($tm, 'number')
        );

        $this->assertCount(
            1,
            $this->getProtectedProperty($tm, 'transactions')
        );

        $transaction->commit();

        $this->assertSame(
            0,
            $this->getProtectedProperty($tm, 'number')
        );

        $this->assertCount(
            0,
            $this->getProtectedProperty($tm, 'transactions')
        );
    }

    /**
     * Tests Phalcon\Mvc\Model\Transaction\Manager :: transaction removed on rollback
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2012-08-07
     *
     * @group mysql
     * @group pgsql
     */
    public function testMvcModelTransactionManagerTransactionRemovedOnRollback(): void
    {
        $tm = $this->container->getShared('transactionManager');

        $transaction = $tm->get();

        $select = new Select();

        $select->setTransaction($transaction);

        $select->assign(
            [
                'sel_name' => 'Crack of Dawn',
            ]
        );

        $select->create();

        $this->assertSame(
            1,
            $this->getProtectedProperty($tm, 'number')
        );

        $this->assertCount(
            1,
            $this->getProtectedProperty($tm, 'transactions')
        );

        try {
            $transaction->rollback();
        } catch (Failed $e) {
            // do nothing
        }

        $this->assertSame(
            0,
            $this->getProtectedProperty($tm, 'number')
        );

        $this->assertCount(
            0,
            $this->getProtectedProperty($tm, 'transactions')
        );
    }
}
