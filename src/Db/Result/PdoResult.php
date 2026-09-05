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

use PDO;
use PDOStatement;
use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Enum;
use Phalcon\Db\ResultInterface;

use function is_object;

/**
 * Encapsulates the resultset internals
 *
 * ```php
 * $result = $connection->query("SELECT * FROM co_invoices ORDER BY inv_title");
 *
 * $result->setFetchMode(
 *     \Phalcon\Db\Enum::FETCH_NUM
 * );
 *
 * while ($invoice = $result->fetchArray()) {
 *     print_r($invoice);
 * }
 * ```
 *
 * @phpstan-import-type db_bind_params from DbTypes
 * @phpstan-import-type db_bind_types from DbTypes
 * @phpstan-import-type db_constructor_arguments from DbTypes
 * @phpstan-import-type db_rows from DbTypes
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
     *            TODO: Check if this property is used
     */
    protected $result;

    protected int | null $rowCount = null;

    /**
     * Phalcon\Db\Result\Pdo constructor
     *
     * @phpstan-param db_bind_params $bindParams
     * @phpstan-param db_bind_types  $bindTypes
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
     *     "SELECT * FROM co_invoices ORDER BY inv_title"
     * );
     *
     * // Move to third row on result
     * $result->dataSeek(2);
     *
     * // Fetch third row
     * $row = $result->fetch();
     *```
     */
    public function dataSeek(int $number): void
    {
        /** @var PDO $pdo */
        $pdo = $this->connection->getInternalHandler();

        /**
         * PDO does not support scrollable cursors, so we need to re-execute the
         * statement
         */
        if (is_array($this->bindParams)) {
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

        /** @var PDOStatement $statement */
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
     */
    public function execute(): bool
    {
        $this->rowCount = null;

        return $this->pdoStatement->execute();
    }

    /**
     * Fetches an array/object of strings that corresponds to the fetched row,
     * or FALSE if there are no more rows. This method is affected by the active
     * fetch flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     *```php
     * $result = $connection->query("SELECT * FROM co_invoices ORDER BY inv_title");
     *
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_OBJ
     * );
     *
     * while ($invoice = $result->fetch()) {
     *     echo $invoice->inv_title;
     * }
     *```
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
     *     "SELECT * FROM co_invoices ORDER BY inv_title"
     * );
     *
     * $invoices = $result->fetchAll();
     *```
     *
     * @param callable|int|string|null $fetchArgument
     *
     * @phpstan-param db_constructor_arguments|null $constructorArgs
     *
     * @phpstan-return db_rows
     */
    public function fetchAll(
        int $mode = Enum::FETCH_DEFAULT,
        mixed $fetchArgument = Enum::FETCH_ORI_NEXT,
        array | null $constructorArgs = null
    ): array {
        if ($mode === Enum::FETCH_CLASS) {
            /** @var class-string $className */
            $className = $fetchArgument;
            /** @var db_rows $rows */
            $rows = $this->pdoStatement->fetchAll($mode, $className, $constructorArgs);

            return $rows;
        }

        if ($mode === Enum::FETCH_COLUMN || $mode === Enum::FETCH_FUNC) {
            /** @var callable(): mixed|int|string $fetchArgument */
            /** @var db_rows $rows */
            $rows = $this->pdoStatement->fetchAll($mode, $fetchArgument);

            return $rows;
        }

        /** @var db_rows $rows */
        $rows = $this->pdoStatement->fetchAll($mode);

        return $rows;
    }

    /**
     * Returns an array of strings that corresponds to the fetched row, or FALSE
     * if there are no more rows. This method is affected by the active fetch
     * flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     *```php
     * $result = $connection->query("SELECT * FROM co_invoices ORDER BY inv_title");
     *
     * $result->setFetchMode(
     *     \Phalcon\Enum::FETCH_NUM
     * );
     *
     * while ($invoice = result->fetchArray()) {
     *     print_r($invoice);
     * }
     *```
     */
    public function fetchArray(): mixed
    {
        return $this->pdoStatement->fetch($this->fetchMode);
    }

    /**
     * Gets the internal PDO result object
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
     *     "SELECT * FROM co_invoices ORDER BY inv_title"
     * );
     *
     * echo "There are ", $result->numRows(), " rows in the resultset";
     *```
     */
    public function numRows(): int
    {
        if (null !== $this->rowCount) {
            return $this->rowCount;
        }

        $type = $this->connection->getType();

        /**
         * MySQL and PostgreSQL properly return the number of records, and keep
         * doing so once the cursor has been advanced
         */
        if ("mysql" === $type || "pgsql" === $type) {
            $this->rowCount = (int) $this->pdoStatement->rowCount();

            return $this->rowCount;
        }

        /**
         * SQLite returns resultsets that to the client eyes (PDO) have an
         * arbitrary number of rows - it is a streaming cursor and does not know
         * the count until the result has been stepped to the end. So the count
         * costs an extra statement.
         *
         * The original statement is wrapped verbatim rather than taken apart
         * and rebuilt: any SELECT is a valid subquery, which keeps multi-line
         * statements and common table expressions working, and makes a wrapped
         * `SELECT COUNT(*) ... GROUP BY` report its number of groups instead of
         * a hard-coded 1.
         */
        $result = $this->connection->query(
            "SELECT COUNT(*) \"numrows\" FROM (" . $this->sqlStatement . ")",
            $this->bindParams,
            $this->bindTypes
        );

        /** @var ResultInterface $result */
        /** @var array{numrows: int|string} $row */
        $row = $result->fetch();

        /**
         * Update the value to avoid further calculations
         */
        $this->rowCount = (int) $row["numrows"];

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
     */
    public function setFetchMode(
        int $fetchMode,
        object | string | null $colNoOrClassNameOrObject = null,
        mixed $ctorargs = null
    ): bool {
        if (Enum::FETCH_CLASS === $fetchMode || Enum::FETCH_INTO === $fetchMode) {
            /** @var object|string $target */
            $target = $colNoOrClassNameOrObject;
            /** @var db_constructor_arguments|null $constructorArgs */
            $constructorArgs = $ctorargs;

            if (!$this->pdoStatement->setFetchMode($fetchMode, $target, $constructorArgs)) {
                return false;
            }
        } elseif (Enum::FETCH_COLUMN === $fetchMode) {
            /** @var string $column */
            $column = $colNoOrClassNameOrObject;

            if (!$this->pdoStatement->setFetchMode($fetchMode, $column)) {
                return false;
            }
        } else {
            if (!$this->pdoStatement->setFetchMode($fetchMode)) {
                return false;
            }
        }

        $this->fetchMode = $fetchMode;

        return true;
    }
}
