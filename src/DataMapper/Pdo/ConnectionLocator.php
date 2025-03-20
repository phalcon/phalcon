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

namespace Phalcon\DataMapper\Pdo;

use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;

use function array_rand;
use function is_callable;

/**
 * Manages Connection instances for default, read, and write connections.
 */
class ConnectionLocator
{
    /**
     * @var callable
     */
    protected mixed $master;

    /**
     * A collection of resolved instances
     */
    private array $instances = [];

    /**
     * Constructor.
     *
     * @param callable                      $master
     * @param array<string, callable|mixed> $read
     * @param array<string, callable|mixed> $write
     *
     * @throws ConnectionNotFound
     */
    public function __construct(
        callable $master,
        protected array $read = [],
        protected array $write = []
    ) {
        $this->setMaster($master);

        foreach ($read as $name => $callableObject) {
            if (!is_callable($callableObject)) {
                throw new ConnectionNotFound(
                    "Read connection [$name] must be a callable"
                );
            }
            $this->setRead($name, $callableObject);
        }

        foreach ($write as $name => $callableObject) {
            if (!is_callable($callableObject)) {
                throw new ConnectionNotFound(
                    "Write connection [$name] must be a callable"
                );
            }
            $this->setWrite($name, $callableObject);
        }
    }

    /**
     * Returns the default connection object.
     *
     * @return Connection
     */
    public function getMaster(): Connection
    {
        if (!isset($this->instances["master"])) {
            $master                    = $this->master;
            $this->instances["master"] = $master();
        }

        return $this->instances["master"];
    }

    /**
     * Returns a read connection by name; if no name is given, picks a
     * random connection; if no read connections are present, returns the
     * default connection.
     *
     * @param string $name
     *
     * @return Connection
     * @throws ConnectionNotFound
     */
    public function getRead(string $name = ""): Connection
    {
        return $this->getConnection("read", $name);
    }

    /**
     * Returns a write connection by name; if no name is given, picks a
     * random connection; if no write connections are present, returns the
     * default connection.
     *
     * @param string $name
     *
     * @return Connection
     * @throws ConnectionNotFound
     */
    public function getWrite(string $name = ""): Connection
    {
        return $this->getConnection("write", $name);
    }

    /**
     * @param mixed $argument
     * @param mixed ...$arguments
     *
     * @return static
     * @throws ConnectionNotFound
     */
    public static function new(mixed $argument, mixed ...$arguments): static
    {
        if ($argument instanceof Connection) {
            $defaultFactory = function () use ($argument) {
                return $argument;
            };

            return new static($defaultFactory);
        }

        return new static(Connection::factory($argument, ...$arguments));
    }

    /**
     * Sets the default connection factory.
     *
     * @param callable $callableObject
     *
     * @return ConnectionLocator
     */
    public function setMaster(callable $callableObject): ConnectionLocator
    {
        $this->master = $callableObject;
        unset($this->instances["master"]);

        return $this;
    }

    /**
     * Sets a read connection factory by name.
     *
     * @param string   $name
     * @param callable $callableObject
     *
     * @return ConnectionLocator
     */
    public function setRead(
        string $name,
        callable $callableObject
    ): ConnectionLocator {
        $this->read[$name] = $callableObject;

        return $this;
    }

    /**
     * Sets a write connection factory by name.
     *
     * @param string   $name
     * @param callable $callableObject
     *
     * @return ConnectionLocator
     */
    public function setWrite(
        string $name,
        callable $callableObject
    ): ConnectionLocator {
        $this->write[$name] = $callableObject;

        return $this;
    }

    /**
     * Returns a connection by name.
     *
     * @param string $type
     * @param string $name
     *
     * @return Connection
     * @throws ConnectionNotFound
     */
    protected function getConnection(
        string $type,
        string $name = ""
    ): Connection {
        $collection = $this->{$type};

        /**
         * No collection returns the master
         */
        if (empty($collection)) {
            return $this->getMaster();
        }

        /**
         * If the requested name is empty, get a random connection
         */
        $requested = $name ?: array_rand($collection);

        /**
         * If the connection name does not exist, send an exception back
         */
        if (!isset($collection[$requested])) {
            throw new ConnectionNotFound(
                "Connection not found: " . $type . ":" . $requested
            );
        }

        /**
         * Check if the connection has been resolved already, if yes return
         * it, otherwise resolve it. The keys in the `resolved` array are
         * formatted as "type-name"
         */
        $instanceName = $type . "-" . $requested;

        if (!isset($this->instances[$instanceName])) {
            $this->instances[$instanceName] = $collection[$requested]();
        }

        return $this->instances[$instanceName];
    }
}
