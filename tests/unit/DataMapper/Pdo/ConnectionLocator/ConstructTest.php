<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\ConnectionLocator;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Pdo\ConnectionLocatorInterface;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionLocatorConstruct(): void
    {
        $connection = $this->getDataMapperConnection();
        $locator    = new ConnectionLocator($connection);

        $this->assertInstanceOf(ConnectionLocatorInterface::class, $locator);
        $this->assertInstanceOf(ConnectionLocator::class, $locator);
    }
}
