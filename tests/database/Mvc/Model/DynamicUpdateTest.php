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

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Support\Collection;
use Phalcon\Support\Settings;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\CustomersMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Customers;
use Phalcon\Tests\Models\CustomersDymanicUpdate;

final class DynamicUpdateTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    public function tearDown(): void
    {
        $this->container['db']->close();
    }

    /**
     * Tests Phalcon\Mvc\Model :: save() with DynamicUpdate Disabled
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-08-11
     *
     * @group mysql
     */
    public function testMvcModelDisableDynamicUpdate(): void
    {
        $connection         = self::getConnection();
        $customersMigration = new CustomersMigration($connection);
        $customersMigration->insert(90, 1);

        $collection = new Collection();

        $connection    = $this->container->get('db');
        $manager       = new Manager();
        $modelsManager = $this->container->get('modelsManager');
        $manager->attach(
            'db:beforeQuery',
            function (Event $event) use ($connection, $collection) {
                $key = (string)$collection->count();
                $collection->set($key, $connection->getSQLVariables());
            }
        );

        $connection->setEventsManager($manager);

        /**
         * Disable system wide dynamic update
         */
        Settings::set('orm.dynamic_update', false);
        $actual = Settings::get('orm.dynamic_update');
        $this->assertFalse($actual);

        /**
         * New model
         */
        $customer                 = Customers::findFirst(['cst_id=:id:', 'bind' => ['id' => 90]]);
        $customer->cst_name_first = 'disableDynamicUpdate';
        $actual                   = $customer->save();

        $this->assertTrue($actual);
        $actual = $modelsManager->isUsingDynamicUpdate($customer);
        $this->assertFalse($actual);

        $collection->clear();

        $customer->cst_name_last = 'cst_test_lastName';

        $actual = $customer->save();
        $this->assertTrue($actual);

        $expected = 4;
        $actual   = count($collection->get('0'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Model :: save() with DynamicUpdate Disabled Cherry pick
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-08-11
     *
     * @group mysql
     */
    public function testMvcModelDisabledCherryPickDynamicUpdate(): void
    {
        $connection         = self::getConnection();
        $customersMigration = new CustomersMigration($connection);
        $customersMigration->insert(90, 1);

        $collection    = new Collection();
        $manager       = new Manager();
        $connection    = $this->container->get('db');
        $modelsManager = $this->container->get('modelsManager');
        $manager->attach(
            'db:beforeQuery',
            function (Event $event) use ($connection, $collection) {
                $key = (string)$collection->count();
                $collection->set($key, $connection->getSQLVariables());
            }
        );

        $connection->setEventsManager($manager);

        /**
         * Disable system wide dynamic update
         */
        Settings::set('orm.dynamic_update', false);
        $actual = Settings::get('orm.dynamic_update');
        $this->assertFalse($actual);

        /**
         * New model
         *
         * @var CustomersDymanicUpdate
         */
        $customer = CustomersDymanicUpdate::findFirst(['cst_id=:id:', 'bind' => ['id' => 90]]);

        $actual = $modelsManager->isUsingDynamicUpdate($customer);
        $this->assertTrue($actual);

        $collection->clear();

        $customer->cst_name_first = 'disabledCherryPickDynamicUpdate';
        $actual                   = $customer->save();
        $this->assertTrue($actual);

        $expected = 2;
        $actual   = count($collection->get('0'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Model :: save() With DynamicUpdate Enabled
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-08-11
     *
     * @issue https://github.com/phalcon/cphalcon/issues/16343
     *
     * @group mysql
     */
    public function testMvcModelEnableDynamicUpdate(): void
    {
        $connection         = self::getConnection();
        $customersMigration = new CustomersMigration($connection);
        $customersMigration->insert(90, 1);

        /**
         * Check system wide Dynamic update
         */
        Settings::set('orm.dynamic_update', true);
        $actual = Settings::get('orm.dynamic_update');
        $this->assertTrue($actual);

        $collection    = new Collection();
        $connection    = $this->container->get('db');
        $manager       = new Manager();
        $modelsManager = $this->container->get('modelsManager');
        $manager->attach(
            'db:beforeQuery',
            function (Event $event) use ($connection, $collection) {
                $key = (string)$collection->count();
                $collection->set($key, $connection->getSQLVariables());
            }
        );

        $connection->setEventsManager($manager);

        /**
         * New model
         */
        $customer                 = Customers::findFirst(['cst_id=:id:', 'bind' => ['id' => 90]]);
        $customer->cst_name_first = 'enableDynamicUpdate';

        $actual = $customer->save();
        $this->assertTrue($actual);

        $actual = $modelsManager->isUsingDynamicUpdate($customer);
        $this->assertTrue($actual);

        $collection->clear();

        $customer->cst_name_last = 'cst_test_lastName';

        $actual = $customer->save();
        $this->assertTrue($actual);

        $expected = 2;
        $actual   = count($collection->get('0'));
        $this->assertEquals($expected, $actual);
    }
}
