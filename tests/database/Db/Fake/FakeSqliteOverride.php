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

namespace Phalcon\Tests\Database\Db\Fake;

use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\DialectInterface;

/**
 * Overrides setDialect() with the cphalcon signature
 */
class FakeSqliteOverride extends Sqlite
{
    public function setDialect(DialectInterface $dialect)
    {
        parent::setDialect($dialect);
    }
}
