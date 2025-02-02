<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use BadMethodCallException;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Pdo\ConnectionFixture;

final class UnderscoreCallTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __call() - exception
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionUnderscoreCallException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            "Class 'Phalcon\DataMapper\Pdo\Connection' does not have a method 'unknown'"
        );

        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $connection->unknown();
    }
}
