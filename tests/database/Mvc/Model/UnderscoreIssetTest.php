<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Database\Mvc\Model;

use PDO;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\CustomersMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models;

final class UnderscoreIssetTest extends DatabaseTestCase
{
    use DiTrait;

    public function tearDown(): void
    {
        $this->container['db']->close();
    }

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model :: __isset()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     *
     * @group  common
     */
    public function testMvcModelUnderscoreIsset(): void
    {
        /** @var PDO $connection */
        $connection = self::getConnection();

        $customersMigration = new CustomersMigration($connection);
        $customersMigration->insert(1, 1, 'test_firstName_1', 'test_lastName_1');

        /**
         * Belongs-to relationship
         */
        $customerSnap = Models\CustomersKeepSnapshots::findFirst();

        $this->assertTrue(
            isset($customerSnap->invoices)
        );

        $this->assertFalse(
            isset($customerSnap->nonExistentRelation)
        );
    }
}
