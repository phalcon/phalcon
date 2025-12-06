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

namespace Phalcon\DataMapper\Pdo\Connection\Traits;

use PDO;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Exception\Exception;

use function current;
use function is_array;

/**
 * Provides array quoting, profiling, a new `perform()` method, new `fetch*()`
 * methods
 */
trait FetchTrait
{
    /**
     * Performs a statement and returns the number of affected rows.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return int
     */
    public function fetchAffected(string $statement, array $values = []): int
    {
        $sth = $this->perform($statement, $values);

        return $sth->rowCount();
    }

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as associative arrays.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAll(string $statement, array $values = []): array
    {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches an associative array of rows from the database; the rows are
     * returned as associative arrays, and the array of rows is keyed on the
     * first column of each row.
     *
     * If multiple rows have the same first column value, the last row with
     * that value will overwrite earlier rows. This method is more resource
     * intensive and should be avoided if possible.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAssoc(string $statement, array $values = []): array
    {
        $data = [];
        $sth  = $this->perform($statement, $values);

        $row = $sth->fetch(PDO::FETCH_ASSOC);
        while ($row) {
            $data[current($row)] = $row;

            $row = $sth->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    /**
     * Fetches a column of rows as a sequential array (default first one).
     *
     * @param string $statement
     * @param array  $values
     * @param int    $column
     *
     * @return array<array-key, string>
     */
    public function fetchColumn(
        string $statement,
        array $values = [],
        int $column = 0
    ): array {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    /**
     * Fetches multiple from the database as an associative array. The first
     * column will be the index key. The default flags are
     * PDO::FETCH_ASSOC | PDO::FETCH_GROUP
     *
     * @param string $statement
     * @param array  $values
     * @param int    $flags
     *
     * @return array
     */
    public function fetchGroup(
        string $statement,
        array $values = [],
        int $flags = PDO::FETCH_ASSOC
    ): array {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_GROUP | $flags);
    }

    /**
     * Fetches one row from the database as an object where the column values
     * are mapped to object properties.
     *
     * Since PDO injects property values before invoking the constructor, any
     * initializations for defaults that you potentially have in your object's
     * constructor, will override the values that have been injected by
     * `fetchObject`. The default object returned is `\stdClass`
     *
     * @param string $statement
     * @param array  $values
     * @param string $className
     * @param array  $arguments
     *
     * @return object
     */
    public function fetchObject(
        string $statement,
        array $values = [],
        string $className = "stdClass",
        array $arguments = []
    ): object {
        $sth = $this->perform($statement, $values);

        return $sth->fetchObject($className, $arguments);
    }

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as objects where the column values are mapped to object
     * properties.
     *
     * Since PDO injects property values before invoking the constructor, any
     * initializations for defaults that you potentially have in your object's
     * constructor, will override the values that have been injected by
     * `fetchObject`. The default object returned is `\stdClass`
     *
     * @param string $statement
     * @param array  $values
     * @param string $class
     * @param array  $arguments
     *
     * @return array
     */
    public function fetchObjects(
        string $statement,
        array $values = [],
        string $class = "stdClass",
        array $arguments = []
    ): array {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_CLASS, $class, $arguments);
    }

    /**
     * Fetches one row from the database as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchOne(string $statement, array $values = []): array
    {
        $sth    = $this->perform($statement, $values);
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        return is_array($result) ? $result : [];
    }

    /**
     * Fetches an associative array of rows as key-value pairs (first column is
     * the key, second column is the value).
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchPairs(string $statement, array $values = []): array
    {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Fetches an associative array of rows uniquely. The rows are returned as
     * associative arrays.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchUnique(string $statement, array $values = []): array
    {
        $sth = $this->perform($statement, $values);

        return $sth->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     * Fetches the first value of the passed column, of the first row
     *
     * @param string $statement
     * @param array  $values
     * @param int    $column
     *
     * @return mixed
     */
    public function fetchValue(
        string $statement,
        array $values = [],
        int $column = 0
    ): mixed {
        $sth = $this->perform($statement, $values);

        return $sth->fetchColumn($column);
    }

    /**
     * Performs a query with bound values and returns the resulting
     * PDOStatement; array values will be passed through `quote()` and their
     * respective placeholders will be replaced in the query string. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     * @throws Exception
     */
    abstract public function perform(
        string $statement,
        array $values = []
    ): PDOStatement;
}
