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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Mysql;

use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListTablesTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Adapter\Pdo\Mysql :: listTables()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-08-03
     */
    public function testListTables(): void
    {
        $this->markTestSkipped('Need implementation');
    }
}
