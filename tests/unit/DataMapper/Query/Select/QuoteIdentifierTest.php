<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Query\Select;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\QueryFactory;

final class QuoteIdentifierTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: quoteIdentifier()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectQuoteIdentifier(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $quotes   = $connection->getQuoteNames();
        $source   = 'some field';
        $expected = $quotes['prefix'] . $source . $quotes['suffix'];
        $actual   = $select->quoteIdentifier($source);
        $this->assertEquals($expected, $actual);
    }
}
