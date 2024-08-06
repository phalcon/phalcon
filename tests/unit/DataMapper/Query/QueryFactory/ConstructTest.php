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
use Phalcon\DataMapper\Query\QueryFactory;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Query\QueryFactory :: __construct()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryQueryFactoryConstruct(): void
    {
        $factory = new QueryFactory();
        $this->assertInstanceOf(QueryFactory::class, $factory);
    }
}
