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

namespace Phalcon\DataMapper\Pdo\Connection;

use BadMethodCallException;
use PDO;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection\Traits\FetchTrait;
use Phalcon\DataMapper\Pdo\Connection\Traits\YieldTrait;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\DataMapper\Pdo\Profiler\ProfilerInterface;

use function get_class;
use function is_array;
use function is_bool;
use function is_int;
use function method_exists;

/**
 * Provides array quoting, profiling, a new `perform()` method, new `fetch*()`
 * methods
 *
 * @property PDO|null               $pdo
 * @property ProfilerInterface|null $profiler
 *
 * @method bool        beginTransaction()
 * @method bool        commit()
 * @method string|null errorCode()
 * @method array       errorInfo()
 * @method mixed       getAttribute(int $attribute)
 * @method string      lastInsertId(string $name = null)
 * @method bool        inTransaction()
 * @method bool        rollback()
 * @method bool        setAttribute(int $attribute, mixed $value)
 */
abstract class AbstractConnection
{
    use FetchTrait;
    use YieldTrait;

    /**
     * @var PDO|null
     */
    protected PDO | null $pdo = null;

    protected ProfilerInterface | null $profiler = null;

    /**
     * Proxies to PDO methods created for specific drivers; in particular,
     * `sqlite` and `pgsql`.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments)
    {
        $this->connect();

        if (true !== method_exists($this->pdo, $method)) {
            $className = get_class($this);
            $message   = "Class '" . $className
                . "' does not have a method '" . $method . "'";

            throw new BadMethodCallException($message);
        }

        $this->profileStart($method);

        $result = $this->pdo->{$method}(...$arguments);

        $this->profileFinish();

        return $result;
    }

    /**
     * Connects to the database.
     */
    abstract public function connect(): void;

    /**
     * Disconnects from the database.
     */
    abstract public function disconnect(): void;

    /**
     * Executes a statement on the PDO adapter. This is not a pass-through
     * because we want to log the statement if the profiler is enabled
     *
     * @param string $statement
     *
     * @return int|false
     */
    public function exec(string $statement = ''): int | false
    {
        $this->connect();
        $this->profileStart(__METHOD__);

        $result = $this->pdo->exec($statement);

        $this->profileFinish($statement);

        return $result;
    }

    /**
     * Return the inner PDO (if any)
     *
     * @return PDO
     */
    public function getAdapter(): PDO
    {
        $this->connect();

        return $this->pdo;
    }

    /**
     * Return an array of available PDO drivers
     */
    public static function getAvailableDrivers(): array
    {
        return PDO::getAvailableDrivers();
    }

    /**
     * Return the driver name
     *
     * @return string
     */
    public function getDriverName(): string
    {
        $this->connect();

        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Returns the Profiler instance.
     *
     * @return ProfilerInterface|null
     */
    public function getProfiler(): ProfilerInterface | null
    {
        return $this->profiler;
    }

    /**
     * Is the PDO connection active?
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return (bool)$this->pdo;
    }

    /**
     * Performs a query with bound values and returns the resulting
     * PDOStatement; array values will be passed through `quote()` and their
     * respective placeholders will be replaced in the query string. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @param string               $statement
     * @param array<string, mixed> $values
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function perform(
        string $statement,
        array $values = []
    ): PDOStatement {
        $this->connect();

        $this->profileStart(__METHOD__);

        $sth = $this->pdo->prepare($statement);
        foreach ($values as $name => $value) {
            $this->performBind($sth, $name, $value);
        }

        $sth->execute();

        $this->profileFinish($statement, $values);

        return $sth;
    }

    /**
     * Prepares an SQL statement for execution.
     *
     * @param string $statement
     * @param array  $options
     *
     * @return PDOStatement
     */
    public function prepare(
        string $statement,
        array $options = []
    ): PDOStatement {
        $this->connect();

        $this->profileStart(__METHOD__);

        $sth = $this->pdo->prepare($statement, $options);

        $this->profileFinish($sth->queryString);

        return $sth;
    }

    /**
     * Queries the database and returns a PDOStatement. If the profiler is
     * enabled, the operation will be recorded.
     *
     * @param string   $statement
     * @param int|null $mode
     * @param mixed    ...$arguments
     *
     * @return PDOStatement|false
     */
    public function query(
        string $statement,
        int | null $mode = null,
        mixed ...$arguments
    ): PDOStatement | false {
        $this->connect();
        $this->profileStart(__METHOD__);

        $sth = $this->pdo->query($statement, $mode, ...$arguments);

        $this->profileFinish($sth->queryString);

        return $sth;
    }

    /**
     * Sets the Profiler instance.
     *
     * @param ProfilerInterface $profiler
     *
     * @return $this
     */
    public function setProfiler(ProfilerInterface $profiler): static
    {
        $this->profiler = $profiler;

        return $this;
    }

    /**
     * Bind a value using the proper PDO::PARAM_* type.
     *
     * @param PDOStatement $statement
     * @param mixed        $name
     * @param mixed        $arguments
     */
    protected function performBind(
        PDOStatement $statement,
        mixed $name,
        mixed $arguments
    ): void {
        $key = is_int($name) ? $name + 1 : $name;

        if (is_array($arguments)) {
            $type = $arguments[1] ?? PDO::PARAM_STR;

            if ($type === PDO::PARAM_BOOL && is_bool($arguments[0])) {
                $arguments[0] = $arguments[0] ? "1" : "0";
            }

            $statement->bindValue($key, $arguments[0], $type);
        } else {
            $statement->bindValue($key, $arguments);
        }
    }

    /**
     * @param string|null $statement
     * @param array       $values
     *
     * @return void
     */
    protected function profileFinish(
        string | null $statement = null,
        array $values = []
    ): void {
        if ($this->profiler) {
            $this->profiler->finish($statement, $values);
        }
    }

    /**
     * @param string $method
     *
     * @return void
     */
    protected function profileStart(string $method): void
    {
        if ($this->profiler) {
            $this->profiler->start($method);
        }
    }
}
