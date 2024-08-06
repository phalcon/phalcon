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

namespace Phalcon\Tests\Database\DataMapper\Query\Select;

use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\DatabaseTestCase;

final class SetFlagTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: setFlag()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectSetFlag(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = new QueryFactory();
        $select     = $factory->newSelect($connection);

        $select
            ->from('co_invoices')
            ->setFlag("LOW_PRIORITY")
        ;

        $expected = "SELECT LOW_PRIORITY * FROM co_invoices";
        $actual   = $select->getStatement();
        $this->assertEquals($expected, $actual);
    }
}
