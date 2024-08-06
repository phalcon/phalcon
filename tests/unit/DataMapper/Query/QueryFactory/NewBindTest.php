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
use Phalcon\DataMapper\Query\Bind;
use Phalcon\DataMapper\Query\QueryFactory;

final class NewBindTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\QueryFactory :: newBind()
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmQueryQueryFactoryNewBind(): void
    {
        $factory = new QueryFactory();
        $bind    = $factory->newBind();
        $this->assertInstanceOf(Bind::class, $bind);
    }
}
