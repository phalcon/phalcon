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

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use PDOStatement;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Database\DataMapper\Fake\FakeConnectionOverride;
use PHPUnit\Framework\Attributes\Group;

#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class OverrideSignatureTest extends AbstractDatabaseTestCase
{
    /**
     * A connection can override prepare() and query() with the cphalcon
     * signatures.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/841
     */
    public function testDMPdoConnectionPrepareQueryOverride(): void
    {
        $connection = new FakeConnectionOverride(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $this->assertInstanceOf(PDOStatement::class, $connection->prepare('SELECT 1'));
        $this->assertInstanceOf(PDOStatement::class, $connection->query('SELECT 1'));
    }
}
