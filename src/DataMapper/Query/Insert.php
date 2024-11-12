<?php

/**
 * $this file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 *
 * Implementation of $this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query;

use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\DataMapper\Statement\Insert as InsertStatement;

/**
 * Insert Query
 */
class Insert extends InsertStatement
{
    /**
     * Create a new instance of this object
     *
     * @param mixed ...$arguments
     *
     * @return static
     */
    public static function new(mixed ...$arguments): static
    {
        $connection = Connection::new(...$arguments);

        return new static($connection->getDriverName(), $connection);
    }

    /**
     * Constructor.
     *
     * @param string     $driver
     * @param Connection $connection
     */
    public function __construct(
        string $driver,
        protected Connection $connection
    ) {
        parent::__construct($driver);
    }

    /**
     * Returns the id of the last inserted record
     *
     * @param string|null $name
     *
     * @return string
     */
    public function getLastInsertId(string $name = null): string
    {
        return $this->connection->lastInsertId($name);
    }

    /**
     * Performs a statement in the connection
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function perform()
    {
        return $this->connection->perform(
            $this->getStatement(),
            $this->getBindValues()
        );
    }
}
