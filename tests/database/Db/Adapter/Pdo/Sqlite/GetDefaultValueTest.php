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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Sqlite;

use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetDefaultValueTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Adapter\Pdo\Sqlite :: getDefaultValue()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  sqlite
     */
    public function testDbAdapterPdoSqliteGetDefaultValue(): void
    {
        $this->markTestSkipped('Need implementation');
    }
}
