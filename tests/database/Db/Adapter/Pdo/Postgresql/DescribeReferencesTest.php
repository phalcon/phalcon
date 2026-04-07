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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Postgresql;

use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

use function env;

final class DescribeReferencesTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo\Postgresql :: describeReferences()
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-09-28
     * @group  pgsql
     */
    public function testDbAdapterPdoPostgresqlDescribeReferences(): void
    {
        $this->markTestSkipped('Need implementation - no FK-constrained tables in current PostgreSQL schema');

        $db = $this->container->get('db');

        $referencesNoSchema = $db->describeReferences('robots_parts');
        $referencesSchema   = $db->describeReferences(
            'robots_parts',
            env('DATA_POSTGRES_SCHEMA')
        );

        $this->assertEquals($referencesNoSchema, $referencesSchema);
        $this->assertCount(2, $referencesNoSchema);

        foreach ($referencesNoSchema as $reference) {
            $this->assertCount(1, $reference->getColumns());
        }

        $referencesSchema   = $db->describeReferences(
            'robots_parts',
            env('DATA_POSTGRES_SCHEMA')
        );

        $this->assertEquals($referencesNoSchema, $referencesSchema);
        $this->assertCount(2, $referencesNoSchema);

        foreach ($referencesNoSchema as $reference) {
            $this->assertCount(1, $reference->getColumns());
        }
    }
}
