<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection\Decorated;

use PDO;
use Phalcon\DataMapper\Pdo\Connection\Decorated;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ConstructTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection\Decorated ::
     * __construct()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionDecoratedConstruct(): void
    {
        $connection = new PDO(
            $this->getDatabaseDsn(),
            $this->getDatabaseUsername(),
            $this->getDatabasePassword()
        );

        $decorated = new Decorated($connection);
        $decorated->connect();

        $this->assertTrue($decorated->isConnected());
        $this->assertInstanceOf(Profiler::class, $decorated->getProfiler());
        $this->assertSame($connection, $decorated->getAdapter());
    }
}
