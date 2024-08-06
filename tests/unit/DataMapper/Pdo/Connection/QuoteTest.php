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

final class QuoteTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: quote()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionQuote(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();
        $quotes     = $connection->getQuoteNames();

        $source   = 'test';
        $expected = $quotes["prefix"] . $source . $quotes["suffix"];
        $actual   = $connection->quote($source);
        $this->assertEquals($expected, $actual);

        $source   = ['test', 1, true, null];
        $expected = $quotes["prefix"] . 'test' . $quotes["suffix"] . ', '
            . $quotes["prefix"] . '1' . $quotes["suffix"] . ', '
            . $quotes["prefix"] . '1' . $quotes["suffix"] . ', '
            . $quotes["prefix"] . '' . $quotes["suffix"];
        $actual   = $connection->quote($source);
        $this->assertEquals($expected, $actual);
    }
}
