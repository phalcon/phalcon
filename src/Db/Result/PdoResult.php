<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Db\Result;

use PDOStatement;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\ResultInterface;

use function is_object;
use function preg_match;
use function str_starts_with;

/**
 * Encapsulates the resultset internals
 *
 * ```php
 * $result = $connection->query("SELECT * FROM robots ORDER BY name");
 *
 * $result->setFetchMode(
 *     \Phalcon\Db\Enum::FETCH_NUM
 * );
 *
 * while ($robot = $result->fetchArray()) {
 *     print_r($robot);
 * }
 * ```
 */
class PdoResult implements ResultInterface
{
    /**
     * Active fetch mode
     *
     * @var int
     */
    protected $fetchMode = Enum::FETCH_DEFAULT;

    /**
     * @var mixed
     * TODO: Check if this property is used
     */
    protected $result;

    /**
     * @var int|null
     */
    protected int | null $rowCount = null;

    /**
     * Phalcon\Db\Result\Pdo constructor
     */
    public function __construct(
        protected AdapterInterface $connection,
        protected PDOStatement $pdoStatement,
        protected string $sqlStatement = "",
        protected array $bindParams = [],
        protected array $bindTypes = []
    ) {
    }

    /**
     * Moves internal resultset cursor to another position letting us to fetch a
     * certain row
     *
     *```php
     * $result = $connection->query(
     *     "SELECT * FROM robots ORDER BY name"
     * );
     *
     * // Move to third row on result
     * $result->dataSeek(2);
     *
     * // Fetch third row
     * $row = $result->fetch();
     *```
     *
     * @param int $number
     *
     * @return void
     */
    public function dataSeek(int $number): void
    {
        $pdo = $this->connection->getInternalHandler();

        /**
         * PDO doesn't support scrollable cursors, so we need to re-execute the
         * statement
         */
        if (!empty($this->bindParams)) {
            $statement = $pdo->prepare($this->sqlStatement);

            if (is_object($statement)) {
                $statement = $this->connection->executePrepared(
                    $statement,
                    $this->bindParams,
                    $this->bindTypes
                );
            }
        } else {
            $statement = $pdo->query($this->sqlStatement);
        }

        $this->pdoStatement = $statement;

        $counter = -1;
        $number--;

        while ($counter !== $number) {
            $statement->fetch($this->fetchMode);
            $counter++;
        }
    }

    /**
     * Allows to execute the statement again. Some database systems don't
     * support scrollable cursors. So, as cursors are forward only, we need to
     * execute the cursor again to fetch rows from the beginning
     *
     * @return bool
     */
    public function execute(): bool
    {
        return $this->pdoStatement->execute();
    }

    /**
     * Fetches an array/object of strings that corresponds to the fetched row,
     * or FALSE if there are no more rows. This method is affected by the active
     * fetch flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     *```php
     * $result = $connection->query("SELECT * FROM robots ORDER BY name");
     *
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_OBJ
     * );
     *
     * while ($robot = $result->fetch()) {
     *     echo $robot->name;
     * }
     *```
     *
     * @param int|null $fetchStyle
     * @param int      $cursorOrientation
     * @param int      $cursorOffset
     *
     * @return mixed
     */
    public function fetch(
        int | null $fetchStyle = null,
        int $cursorOrientation = Enum::FETCH_ORI_NEXT,
        int $cursorOffset = 0
    ): mixed {
        $mode = (null === $fetchStyle) ? $this->fetchMode : $fetchStyle;

        return $this->pdoStatement->fetch(
            $mode,
            $cursorOrientation,
            $cursorOffset
        );
    }

    /**
     * Returns an array of arrays containing all the records in the result
     * This method is affected by the active fetch flag set using
     * `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     *```php
     * $result = $connection->query(
     *     "SELECT * FROM robots ORDER BY name"
     * );
     *
     * $robots = $result->fetchAll();
     *```
     *
     * @param int                      $mode
     * @param int|string|callable|null $fetchArgument
     * @param array|null               $constructorArgs
     *
     * @return array
     */
    public function fetchAll(
        int $mode = Enum::FETCH_DEFAULT,
        mixed $fetchArgument = Enum::FETCH_ORI_NEXT,
        array | null $constructorArgs = null
    ): array {
        if ($mode === Enum::FETCH_CLASS) {
            return $this->pdoStatement->fetchAll($mode, $fetchArgument, $constructorArgs);
        }

        if ($mode === Enum::FETCH_COLUMN || $mode === Enum::FETCH_FUNC) {
            return $this->pdoStatement->fetchAll($mode, $fetchArgument);
        }

        return $this->pdoStatement->fetchAll($mode);
    }

    /**
     * Returns an array of strings that corresponds to the fetched row, or FALSE
     * if there are no more rows. This method is affected by the active fetch
     * flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     *```php
     * $result = $connection->query("SELECT * FROM robots ORDER BY name");
     *
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_NUM
     * );
     *
     * while ($robot = result->fetchArray()) {
     *     print_r($robot);
     * }
     *```
     *
     * @return array
     */
    public function fetchArray(): array
    {
        return $this->pdoStatement->fetch($this->fetchMode);
    }

    /**
     * Gets the internal PDO result object
     *
     * @return PDOStatement
     */
    public function getInternalResult(): PDOStatement
    {
        return $this->pdoStatement;
    }

    /**
     * Gets number of rows returned by a resultset
     *
     *```php
     * $result = $connection->query(
     *     "SELECT * FROM robots ORDER BY name"
     * );
     *
     * echo "There are ", $result->numRows(), " rows in the resultset";
     *```
     */
    public function numRows(): int
    {
        if (null === $this->rowCount) {
            $rowCount = null;

            /**
             * MySQL and PostgreSQL properly returns the number of records
             */
            if (
                "mysql" === $this->connection->getType() ||
                "pgsql" === $this->connection->getType()
            ) {
                $rowCount = $this->pdoStatement->rowCount();
            }

            /**
             * We should get the count using a new statement :(
             */
            if (null === $rowCount) {
                /**
                 * SQLite/SQLServer returns resultsets that to the client eyes
                 * (PDO) has an arbitrary number of rows, so we need to perform
                 * an extra count to know that
                 */
                $rowCount = 1;
                $matches  = [];

                /**
                 * If the sql_statement starts with SELECT COUNT(*) we don't
                 * make the count
                 */
                if (
                    true !== str_starts_with($this->sqlStatement, "SELECT COUNT(*) ") &&
                    preg_match(
                        "/^SELECT\\s+(.*)/i",
                        $this->sqlStatement,
                        $matches
                    )
                ) {
                    $result = $this->connection->query(
                        "SELECT COUNT(*) \"numrows\" FROM (SELECT " . $matches[1] . ")",
                        $this->bindParams,
                        $this->bindTypes
                    );

                    $row      = $result->fetch();
                    $rowCount = (int)$row["numrows"];
                }
            }

            /**
             * Update the value to avoid further calculations
             */
            $this->rowCount = $rowCount;
        }

        return $this->rowCount;
    }

    /**
     * Changes the fetching mode affecting Phalcon\Db\Result\Pdo::fetch()
     *
     *```php
     * // Return array with integer indexes
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_NUM
     * );
     *
     * // Return associative array without integer indexes
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_ASSOC
     * );
     *
     * // Return associative array together with integer indexes
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_BOTH
     * );
     *
     * // Return an object
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_OBJ
     * );
     *```
     *
     * @param int                $fetchMode
     * @param object|string|null $colNoOrClassNameOrObject
     * @param mixed|null         $ctorargs
     *
     * @return bool
     */
    public function setFetchMode(
        int $fetchMode,
        null | object | string $colNoOrClassNameOrObject = null,
        mixed $ctorargs = null
    ): bool {
        if (
            (
                (Enum::FETCH_CLASS === $fetchMode || Enum::FETCH_INTO === $fetchMode) &&
                !$this->pdoStatement->setFetchMode($fetchMode, $colNoOrClassNameOrObject, $ctorargs)
            ) ||
            (
                Enum::FETCH_COLUMN === $fetchMode &&
                !$this->pdoStatement->setFetchMode($fetchMode, $colNoOrClassNameOrObject)
            ) ||
            (!$this->pdoStatement->setFetchMode($fetchMode))
        ) {
            return false;
        }

        $this->fetchMode = $fetchMode;

        return true;
    }
}
