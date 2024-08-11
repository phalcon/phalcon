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
use Phalcon\DataMapper\Pdo\ConnectionLocatorInterface;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ConstructTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\ConnectionLocator :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionLocatorConstruct(): void
    {
        $connection = self::getDataMapperConnection();
        $locator    = new ConnectionLocator($connection);

        $this->assertInstanceOf(ConnectionLocatorInterface::class, $locator);
        $this->assertInstanceOf(ConnectionLocator::class, $locator);
    }
}
