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

final class GetQuoteNamesTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getQuoteNames()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionGetQuoteNames(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $mysql   = [
            "prefix"  => '`',
            "suffix"  => '`',
            "find"    => '`',
            "replace" => '``',
        ];
        $sqlsrv  = [
            "prefix"  => '[',
            "suffix"  => ']',
            "find"    => ']',
            "replace" => '][',
        ];
        $default = [
            "prefix"  => '"',
            "suffix"  => '"',
            "find"    => '"',
            "replace" => '""',
        ];

        $actual = $connection->getQuoteNames('unknown');
        $this->assertEquals($default, $actual);

        $actual = $connection->getQuoteNames('mysql');
        $this->assertEquals($mysql, $actual);

        $actual = $connection->getQuoteNames('sqlsrv');
        $this->assertEquals($sqlsrv, $actual);
    }
}
