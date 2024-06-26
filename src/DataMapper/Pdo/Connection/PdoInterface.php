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

use Generator;
use PDO;
use PDOStatement;

/**
 * An interface to the native PDO object.
 */
interface PdoInterface
{
    /**
     * Begins a transaction. If the profiler is enabled, the operation will
     * be recorded.
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commits the existing transaction. If the profiler is enabled, the
     * operation will be recorded.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Gets the most recent error code.
     *
     * @return string|null
     */
    public function errorCode(): string | null;

    /**
     * Gets the most recent error info.
     *
     * @return array
     */
    public function errorInfo(): array;

    /**
     * Executes an SQL statement and returns the number of affected rows. If
     * the profiler is enabled, the operation will be recorded.
     *
     * @param string $statement
     *
     * @return int|false
     */
    public function exec(string $statement): int | false;

    /**
     * Performs a statement and returns the number of affected rows.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return int
     */
    public function fetchAffected(string $statement, array $values = []): int;

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as associative arrays.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchAll(string $statement, array $values = []): array;

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
    public function fetchAssoc(string $statement, array $values = []): array;

    /**
     * Fetches a column of rows as a sequential array (default first one).
     *
     * @param string $statement
     * @param array  $values
     * @param int    $column
     *
     * @return array
     */
    public function fetchColumn(
        string $statement,
        array $values = [],
        int $column = 0
    ): array;

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
    ): array;

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
    ): object;

    /**
     * Fetches a sequential array of rows from the database; the rows are
     * returned as objects where the column values are mapped to object
     * properties. Since PDO injects property values before invoking the
     * constructor, any initializations for defaults that you potentially have
     * in your object constructor, will override the values that have been
     * injected by `fetchObject`. The default object returned is `\stdClass`
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
    ): array;

    /**
     * Fetches one row from the database as an associative array.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchOne(string $statement, array $values = []): array;

    /**
     * Fetches an associative array of rows as key-value pairs (first column is
     * the key, second column is the value).
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchPairs(string $statement, array $values = []): array;

    /**
     * Fetches data with the FETCH_UNIQUE flag
     *
     * @param string $statement
     * @param array  $values
     *
     * @return array
     */
    public function fetchUnique(string $statement, array $values = []): array;

    /**
     * Fetches the very first value (i.e., first column of the first row).
     *
     * @param string $statement
     * @param array  $values
     *
     * @return mixed
     */
    public function fetchValue(string $statement, array $values = []): mixed;

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     *
     * @return bool|int|string|array|null
     */
    public function getAttribute(int $attribute): bool | int | string | array | null;

    /**
     * Return an array of available PDO drivers (empty array if none available)
     *
     * @return array
     */
    public static function getAvailableDrivers(): array;

    /**
     * Is a transaction currently active? If the profiler is enabled, the
     * operation will be recorded. If the profiler is enabled, the operation
     * will be recorded.
     *
     * @return bool
     */
    public function inTransaction(): bool;

    /**
     * Returns the last inserted autoincrement sequence value. If the profiler
     * is enabled, the operation will be recorded.
     *
     * @param string|null $name
     *
     * @return string|false
     */
    public function lastInsertId(string $name = null): string | false;

    /**
     * Prepares an SQL statement for execution.
     *
     * @param string $statement
     * @param array  $options
     *
     * @return PDOStatement|false
     */
    public function prepare(
        string $statement,
        array $options = []
    ): PDOStatement | false;

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
        ?int $mode = null,
        mixed ...$arguments
    ): PDOStatement | false;

    /**
     * Quotes a value for use in an SQL statement. This differs from
     * `PDO::quote()` in that it will convert an array into a string of
     * comma-separated quoted values. The default type is `PDO::PARAM_STR`
     *
     * @param string|int|array|float|null $value
     * @param int                         $type
     *
     * @return string|false
     */
    public function quote(
        string | int | array | float | null $value,
        int $type = PDO::PARAM_STR
    ): string | false;

    /**
     * Rolls back the current transaction, and restores autocommit mode. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @return bool
     */
    public function rollBack(): bool;

    /**
     * Set a database connection attribute
     *
     * @param int   $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function setAttribute(int $attribute, mixed $value): bool;

    /**
     * Yield results using fetchAll
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     */
    public function yieldAll(string $statement, array $values = []): Generator;

    /**
     * Yield results using fetchAssoc
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     */
    public function yieldAssoc(
        string $statement,
        array $values = []
    ): Generator;

    /**
     * Yield results using fetchColumns
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     */
    public function yieldColumns(
        string $statement,
        array $values = []
    ): Generator;

    /**
     * Yield objects where the column values are mapped to object properties.
     *
     * Warning: PDO "injects property-values BEFORE invoking the constructor -
     * in other words, if your class initializes property-values to defaults
     * in the constructor, you will be overwriting the values injected by
     * fetchObject() !"
     * <https://www.php.net/manual/en/pdostatement.fetchobject.php#111744>
     *
     * @param string $statement
     * @param array  $values
     * @param string $class
     * @param array  $arguments
     *
     * @return Generator
     */
    public function yieldObjects(
        string $statement,
        array $values = [],
        string $class = 'stdClass',
        array $arguments = []
    ): Generator;

    /**
     * Yield key-value pairs (key => value)
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     */
    public function yieldPairs(
        string $statement,
        array $values = []
    ): Generator;

    /**
     * Yield unique data
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     */
    public function yieldUnique(
        string $statement,
        array $values = []
    ): Generator;
}
