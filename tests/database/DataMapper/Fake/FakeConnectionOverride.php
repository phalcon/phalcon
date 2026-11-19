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

namespace Phalcon\Tests\Database\DataMapper\Fake;

use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection;

/**
 * Overrides prepare() and query() with the cphalcon signatures
 */
class FakeConnectionOverride extends Connection
{
    public function prepare(string $statement, array $options = []): bool | PDOStatement
    {
        return parent::prepare($statement, $options);
    }

    public function query(string $statement): bool | PDOStatement
    {
        return parent::query($statement);
    }
}
