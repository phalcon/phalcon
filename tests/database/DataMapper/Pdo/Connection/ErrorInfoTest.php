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

final class ErrorInfoTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: errorInfo()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionErrorInfo(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $actual = $connection->errorInfo();
        $expect = ['', null, null];

        $this->assertSame($expect, $actual);
    }
}
