<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Info\Adapter\Pgsql;

use Phalcon\DataMapper\Info\Adapter\Pgsql;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetAutoIncSequenceTest extends AbstractDatabaseTestCase
{
    /**
     * @return void
     * @throws Exception
     * @since  2025-01-14
     *
     * @group  pgsql
     *
     */
    public function testDmInfoAdapterPgsqlGetAutoIncSequence(): void
    {
        $connection = self::getDataMapperConnection();

        $pgsql  = new Pgsql($connection);
        $schema = $pgsql->getCurrentSchema();

        $expected = 'field_primary';
        $actual   = $pgsql->getAutoincSequence($schema, 'co_dialect');
        $this->assertSame($expected, $actual);

        $actual = $pgsql->getAutoincSequence($schema, 'co_rb_test_model');
        $this->assertNull($actual);
    }
}
