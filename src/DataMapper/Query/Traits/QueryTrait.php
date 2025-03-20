<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Query
 * @license https://github.com/atlasphp/Atlas.Query/blob/2.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query\Traits;

use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Exception\Exception;

/**
 * @method string               getStatement()
 * @method array<string, mixed> getBindValues()
 */
trait QueryTrait
{
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
     * Create a new instance of this object
     *
     * @param Connection|string $argument
     * @param mixed             ...$arguments
     *
     * @return static
     */
    public static function new(mixed $argument, mixed ...$arguments): static
    {
        if ($argument instanceof Connection) {
            $connection = $argument;
        } else {
            $connection = Connection::new($argument, ...$arguments);
        }

        return new static($connection->getDriverName(), $connection);
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
