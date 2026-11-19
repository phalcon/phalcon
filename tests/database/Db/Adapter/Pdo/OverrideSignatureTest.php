<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Adapter\Pdo;

use Phalcon\Db\Dialect\Sqlite;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Database\Db\Fake\FakeSqliteOverride;
use PHPUnit\Framework\Attributes\Group;

#[Group('sqlite')]
final class OverrideSignatureTest extends AbstractDatabaseTestCase
{
    /**
     * An adapter can override setDialect() with the cphalcon signature.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testDbAdapterSetDialectOverride(): void
    {
        $adapter = new FakeSqliteOverride(self::getDatabaseOptions());
        $dialect = new Sqlite();

        $adapter->setDialect($dialect);

        $this->assertSame($dialect, $adapter->getDialect());
    }
}
