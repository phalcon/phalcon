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
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;
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

        $this->assertInstanceOf(ConnectionLocator::class, $locator);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct() -
     * exception
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionLocatorConstructReadException(): void
    {
        $this->expectException(ConnectionNotFound::class);
        $this->expectExceptionMessage('Read connection [read1] must be a callable');

        $connection = self::getDataMapperConnection();
        (new ConnectionLocator($connection, ['read1' => '123']));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct() -
     * exception
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionLocatorConstructWriteException(): void
    {
        $this->expectException(ConnectionNotFound::class);
        $this->expectExceptionMessage('Write connection [write1] must be a callable');

        $connection = self::getDataMapperConnection();
        (new ConnectionLocator($connection, [], ['write1' => '123']));
    }
}
