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
use Phalcon\Tests\AbstractDatabaseTestCase;

use function spl_object_hash;

final class GetSetMasterTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator ::
     * getMaster()/setMaster()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionLocatorGetSetMaster(): void
    {
        $connection1 = function () {
            return self::getDataMapperConnection();
        };
        $connection2 = function () {
            return self::getDataMapperConnection();
        };
        $locator     = new ConnectionLocator($connection1);

        $expected = spl_object_hash($connection1());
        $actual   = spl_object_hash($locator->getMaster());
        $this->assertSame($expected, $actual);

        $locator->setMaster($connection2);

        $expected = spl_object_hash($connection2());
        $actual   = spl_object_hash($locator->getMaster());
        $this->assertSame($expected, $actual);
    }
}
