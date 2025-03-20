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

namespace Phalcon\Db;

use PDOStatement;

/**
 * Interface for Phalcon\Db\Result objects
 */
interface ResultInterface
{
    /**
     * Moves internal resultset cursor to another position letting us to fetch a
     * certain row
     *
     * @param int $number
     *
     * @return mixed
     */
    public function dataSeek(int $number);

    /**
     * Allows to execute the statement again. Some database systems don't
     * support scrollable cursors. So, as cursors are forward only, we need to
     * execute the cursor again to fetch rows from the beginning
     *
     * @return bool
     */
    public function execute(): bool;

    /**
     * Fetches an array/object of strings that corresponds to the fetched row,
     * or FALSE if there are no more rows. This method is affected by the active
     * fetch flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     * @return mixed
     */
    public function fetch(): mixed;

    /**
     * Returns an array of arrays containing all the records in the result. This
     * method is affected by the active fetch flag set using
     * `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     * @param int        $mode
     * @param mixed      $fetchArgument
     * @param array|null $constructorArgs
     *
     * @return array
     */
    public function fetchAll(
        int $mode = Enum::FETCH_DEFAULT,
        mixed $fetchArgument = Enum::FETCH_ORI_NEXT,
        array | null $constructorArgs = null
    ): array;

    /**
     * Returns an array of strings that corresponds to the fetched row, or FALSE
     * if there are no more rows. This method is affected by the active fetch
     * flag set using `Phalcon\Db\Result\Pdo::setFetchMode()`
     *
     * @return array
     */
    public function fetchArray(): array;

    /**
     * Gets the internal PDO result object
     *
     * @return PDOStatement
     */
    public function getInternalResult(): PDOStatement;

    /**
     * Gets number of rows returned by a resultset
     *
     * @return int
     */
    public function numRows(): int;

    /**
     * Changes the fetching mode affecting Phalcon\Db\Result\Pdo::fetch()
     *
     * @param int $fetchMode
     *
     * @return bool
     */
    public function setFetchMode(int $fetchMode): bool;
}
