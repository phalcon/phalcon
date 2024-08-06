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

use Phalcon\DataMapper\Query\Bind;
use Phalcon\DataMapper\Query\Select;
use Phalcon\Tests\DatabaseTestCase;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\Select :: __construct()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQuerySelectConstruct(): void
    {
        $connection = self::getDataMapperConnection();
        $bind       = new Bind();
        $select     = new Select($connection, $bind);

        $this->assertInstanceOf(Select::class, $select);
    }
}
