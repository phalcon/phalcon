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
use Phalcon\DataMapper\Pdo\Connection;

final class ErrorCodeTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: errorCode()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionErrorCode(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $actual = $connection->errorCode();
        $this->assertEquals('', $actual);
    }
}
