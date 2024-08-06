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
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\CustomersDefaults;

final class HasChangedTest extends DatabaseTestCase
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
     * Tests Phalcon\Mvc\Model :: hasChanged()
     *
     * @author Balázs Németh <https://github.com/zsilbi>
     * @since  2019-05-17
     *
     * @group  common
     */
    public function testMvcModelHasChanged(): void
    {
        /**
         * CustomersDefault has default values
         */
        $customer = new CustomersDefaults();

        $this->assertFalse(
            $customer->hasSnapshotData()
        );

        $this->assertTrue(
            $customer->create()
        );

        $this->assertTrue(
            $customer->hasSnapshotData()
        );

        /**
         * cst_id         has been null previously
         * cst_name_first remains default
         * cst_name_last  changes from default
         */
        $customer->cst_id         = 987;
        $customer->cst_name_first = 'cst_default_firstName';
        $customer->cst_name_last  = 'cst_new_lastName';

        $this->assertTrue(
            $customer->hasChanged('cst_id')
        );

        $this->assertFalse(
            $customer->hasChanged('cst_name_first')
        );

        $this->assertTrue(
            $customer->hasChanged('cst_name_last')
        );

        $this->assertTrue(
            $customer->hasChanged(['cst_name_first', 'cst_name_last'])
        );

        $this->assertFalse(
            $customer->hasChanged(['cst_name_first', 'cst_name_last'], true)
        );
    }
}
