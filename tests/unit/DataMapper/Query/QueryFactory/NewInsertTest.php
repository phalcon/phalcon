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

namespace Phalcon\Tests\Unit\DataMapper\Query\QueryFactory;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\QueryFactory;

final class NewInsertTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\QueryFactory :: newInsert()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQueryQueryFactoryNewInsert(): void
    {
        $connection = $this->getDataMapperConnection();
        $factory    = new QueryFactory();
        $insert     = $factory->newInsert($connection);
        $this->assertInstanceOf(Insert::class, $insert);
    }
}
