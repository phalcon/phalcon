<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use PDO;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\DatabaseTestCase;

final class DebugInfoTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __debugInfo()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionDebugInfo(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $expected = [
            'arguments' => [
                $this->getDatabaseDsn(),
                '****',
                '****',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
                [],
            ],
        ];
        $this->assertEquals($expected, $connection->__debugInfo());
    }
}
