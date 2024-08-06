<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\ConnectionLocator;

use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\Tests\DatabaseTestCase;

use function spl_object_hash;

final class GetSetMasterTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator ::
     * getMaster()/setMaster()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionLocatorGetSetMaster(): void
    {
        $connection1 = $this->getDataMapperConnection();
        $connection2 = $this->getDataMapperConnection();
        $locator     = new ConnectionLocator($connection1);

        $actual = $locator->getMaster();
        $this->assertEquals(spl_object_hash($connection1), spl_object_hash($actual));

        $locator->setMaster($connection2);
        $actual = $locator->getMaster();
        $this->assertEquals(spl_object_hash($connection2), spl_object_hash($actual));
    }
}
