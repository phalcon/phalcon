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
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query;

use BadMethodCallException;
use Generator as Gen;
use PDO;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\DataMapper\Statement\Select as SelectStatement;

use function array_merge;
use function call_user_func_array;

/**
 * Select Query
 *
 * @method int    fetchAffected()
 * @method array  fetchAll()
 * @method array  fetchAssoc()
 * @method array  fetchColumn(int $column = 0)
 * @method array  fetchGroup(int $flags = PDO::FETCH_ASSOC)
 * @method object fetchObject(string $class = "stdClass", array $arguments = [])
 * @method array  fetchObjects(string $class = "stdClass", array $arguments = [])
 * @method array  fetchOne()
 * @method array  fetchPairs()
 * @method array  fetchUnique()
 * @method mixed  fetchValue()
 * @method Gen    yieldAll()
 * @method Gen    yieldAssoc()
 * @method Gen    yieldColumn()
 * @method Gen    yieldObjects(string $class = 'stdClass', array $arguments = [])
 * @method Gen    yieldPairs()
 * @method Gen    yieldUnique()
 */
class Select extends SelectStatement
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
     * Proxied methods to the connection
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $proxied = [
            'fetchAffected' => true,
            'fetchAll'      => true,
            'fetchAssoc'    => true,
            'fetchColumn'   => true,
            'fetchGroup'    => true,
            'fetchObject'   => true,
            'fetchObjects'  => true,
            'fetchOne'      => true,
            'fetchPairs'    => true,
            'fetchUnique'   => true,
            'fetchValue'    => true,
            'yieldAffected' => true,
            'yieldAll'      => true,
            'yieldAssoc'    => true,
            'yieldColumn'   => true,
            'yieldObjects'  => true,
            'yieldPairs'    => true,
            'yieldUnique'   => true,
        ];

        if (isset($proxied[$method])) {
            return call_user_func_array(
                [
                    $this->connection,
                    $method,
                ],
                array_merge(
                    [
                        $this->getStatement(),
                        $this->getBindValues(),
                    ],
                    $params
                )
            );
        }

        throw new BadMethodCallException(
            "Unknown method: [" . $method . "]"
        );
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
