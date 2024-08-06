<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\DataMapper\Pdo\Connection;

final class GetAvailableDriversTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getAvailableDrivers()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionGetAvailableDrivers(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $expected = PDO::getAvailableDrivers();
        $actual   = $connection::getAvailableDrivers();

        $this->assertEquals($expected, $actual);
    }
}
