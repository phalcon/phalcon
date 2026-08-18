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

use Phalcon\Contracts\Events\EventsAware;
use Phalcon\DataMapper\Pdo\Connection\ConnectionInterface;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;
use Phalcon\Events\Traits\EventsAwareTrait;

use function array_rand;
use function call_user_func;

/**
 * Manages Connection instances for default, read, and write connections.
 *
 * The locator gives its events manager to each connection that it returns,
 * so connections that are built on demand also fire the DataMapper events.
 */
class ConnectionLocator implements ConnectionLocatorInterface, EventsAware
{
    use EventsAwareTrait;

    /**
     * A default Connection connection factory/instance.
     */
    protected ConnectionInterface $master;

    /**
     * A registry of Connection "read" factories/instances.
     */
    protected array $read = [];

    /**
     * A registry of Connection "write" factories/instances.
     */
    protected array $write = [];

    /**
     * A collection of resolved instances
     */
    private array $instances = [];

    /**
     * Constructor.
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
     */
    public function getMaster(): ConnectionInterface
    {
        return $this->applyEventsManager($this->master);
    }

    /**
     * Returns a read connection by name; if no name is given, picks a
     * random connection; if no read connections are present, returns the
     * default connection.
     */
    public function getRead(string $name = ""): ConnectionInterface
    {
        return $this->getConnection("read", $name);
    }

    /**
     * Returns a write connection by name; if no name is given, picks a
     * random connection; if no write connections are present, returns the
     * default connection.
     */
    public function getWrite(string $name = ""): ConnectionInterface
    {
        return $this->getConnection("write", $name);
    }

    /**
     * Sets the default connection factory.
     */
    public function setMaster(ConnectionInterface $callableObject): static
    {
        $this->master = $callableObject;

        return $this;
    }

    /**
     * Sets a read connection factory by name.
     */
    public function setRead(
        string $name,
        callable $callableObject
    ): static {
        $this->read[$name] = $callableObject;

        return $this;
    }

    /**
     * Sets a write connection factory by name.
     */
    public function setWrite(
        string $name,
        callable $callableObject
    ): static {
        $this->write[$name] = $callableObject;

        return $this;
    }

    /**
     * Returns a connection by name.
     */
    protected function getConnection(
        string $type,
        string $name = ""
    ): ConnectionInterface {
        $collection = $this->{$type};
        $requested  = $name;
        $instances  = $this->instances;

        /**
         * No collection returns the master
         */
        if (empty($collection)) {
            return $this->getMaster();
        }

        /**
         * If the requested name is empty, get a random connection
         */
        if ("" === $requested) {
            $requested = array_rand($collection);
        }

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

        if (!isset($instances[$instanceName])) {
            $instances[$instanceName] = call_user_func($collection[$requested]);
            $this->instances          = $instances;
        }

        return $this->applyEventsManager($instances[$instanceName]);
    }

    /**
     * Gives the locator's events manager to a connection. Does nothing when
     * the locator has no manager, or when the connection does not accept
     * one. It is safe to call this more than once on the same connection.
     *
     * @param ConnectionInterface $connection
     *
     * @return ConnectionInterface
     */
    private function applyEventsManager(
        ConnectionInterface $connection
    ): ConnectionInterface {
        if (
            null !== $this->eventsManager &&
            $connection instanceof EventsAware
        ) {
            $connection->setEventsManager($this->eventsManager);
        }

        return $connection;
    }
}
