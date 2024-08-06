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

final class GetSetAttributeTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection ::
     * getAttribute()/setAttribute()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionGetSetAttribute(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $this->assertEquals(
            PDO::ERRMODE_EXCEPTION,
            $connection->getAttribute(PDO::ATTR_ERRMODE)
        );

        $connection->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING
        );

        $this->assertEquals(
            PDO::ERRMODE_WARNING,
            $connection->getAttribute(PDO::ATTR_ERRMODE)
        );
    }
}
