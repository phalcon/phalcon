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

namespace Phalcon\Tests\Unit\DataMapper\Query\Update;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\QueryFactory;

final class HasColumnsTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Update :: hasColumns()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryUpdateHasColumns(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $update     = $factory->newUpdate($connection);

        $actual = $update->hasColumns();
        $this->assertFalse($actual);

        $update->columns(['inv_id', 'inv_total']);

        $actual = $update->hasColumns();
        $this->assertTrue($actual);
    }
}
