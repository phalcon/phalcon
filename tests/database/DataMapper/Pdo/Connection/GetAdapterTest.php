<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetAdapterTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getAdapter()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionGetAdapter(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $this->assertFalse($connection->isConnected());
        $this->assertNotNull($connection->getAdapter());
    }
}
