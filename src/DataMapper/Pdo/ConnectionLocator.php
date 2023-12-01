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

use Phalcon\DataMapper\Pdo\Connection\ConnectionInterface;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;

/**
 * Manages Connection instances for default, read, and write connections.
 */
class ConnectionLocator implements ConnectionLocatorInterface
{
    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $master;

    /**
     * @var array
     */
    protected array $read = [];

    /**
     * @var array
     */
    protected array $write = [];

    /**
     * A collection of resolved instances
     *
     * @var array
     */
    private array $instances = [];

    /**
     * Constructor.
     *
     * @param ConnectionInterface $master
     * @param array               $read
     * @param array               $write
     */
    public function __construct(
        ConnectionInterface $master,
        array $read = [],
        array $write = []
    ) {
        $this->setMaster($master);

        foreach ($read as $name => $callableObject) {
            $this->setRead($name, $callableObject);
        }

        foreach ($write as $name => $callableObject) {
            $this->setWrite($name, $callableObject);
        }
    }

    /**
     * Returns the default connection object.
     *
     * @return ConnectionInterface
     */
    public function getMaster(): ConnectionInterface
    {
        return $this->master;
    }

    /**
     * Returns a read connection by name; if no name is given, picks a
     * random connection; if no read connections are present, returns the
     * default connection.
     *
     * @param string $name
     *
     * @return ConnectionInterface
     * @throws ConnectionNotFound
     */
    public function getRead(string $name = ""): ConnectionInterface
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
     * @return ConnectionInterface
     * @throws ConnectionNotFound
     */
    public function getWrite(string $name = ""): ConnectionInterface
    {
        return $this->getConnection("write", $name);
    }

    /**
     * Sets the default connection factory.
     *
     * @param ConnectionInterface $callableObject
     *
     * @return ConnectionLocatorInterface
     */
    public function setMaster(
        ConnectionInterface $callableObject
    ): ConnectionLocatorInterface {
        $this->master = $callableObject;

        return $this;
    }

    /**
     * Sets a read connection factory by name.
     *
     * @param string   $name
     * @param callable $callableObject
     *
     * @return ConnectionLocatorInterface
     */
    public function setRead(
        string $name,
        callable $callableObject
    ): ConnectionLocatorInterface {
        $this->read[$name] = $callableObject;

        return $this;
    }

    /**
     * Sets a write connection factory by name.
     *
     * @param string   $name
     * @param callable $callableObject
     *
     * @return ConnectionLocatorInterface
     */
    public function setWrite(
        string $name,
        callable $callableObject
    ): ConnectionLocatorInterface {
        $this->write[$name] = $callableObject;

        return $this;
    }

    /**
     * Returns a connection by name.
     *
     * @param string $type
     * @param string $name
     *
     * @return ConnectionInterface
     * @throws ConnectionNotFound
     */
    protected function getConnection(
        string $type,
        string $name = ""
    ): ConnectionInterface {
        $collection = $this->{$type};
        $requested  = $name;

        /**
         * No collection returns the master
         */
        if (true === empty($collection)) {
            return $this->getMaster();
        }

        /**
         * If the requested name is empty, get a random connection
         */
        if (true === empty($name)) {
            $requested = array_rand($collection);
        }

        /**
         * If the connection name does not exist, send an exception back
         */
        if (true !== isset($collection[$requested])) {
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

        if (true !== isset($this->instances[$instanceName])) {
            $this->instances[$instanceName] = call_user_func(
                $collection[$requested]
            );
        }

        return $this->instances[$instanceName];
    }
}
