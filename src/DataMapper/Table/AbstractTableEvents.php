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
 * @link    https://github.com/atlasphp/Atlas.Table
 * @license https://github.com/atlasphp/Atlas.Table/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Table;

use PDOStatement;
use Phalcon\DataMapper\Query\Delete;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Update;

abstract class AbstractTableEvents
{
    /**
     * Runs after the row specific Delete query is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Delete        $delete
     * @param PDOStatement  $pdoStatement
     *
     * @return void
     */
    public function afterDeleteRow(
        AbstractTable $table,
        AbstractRow $row,
        Delete $delete,
        PDOStatement $pdoStatement
    ): void {
    }

    /**
     * Runs after a row specific Insert query is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Insert        $insert
     * @param PDOStatement  $pdoStatement
     *
     * @return void
     */
    public function afterInsertRow(
        AbstractTable $table,
        AbstractRow $row,
        Insert $insert,
        PDOStatement $pdoStatement
    ): void {
    }

    /**
     * Runs after the row specific Update query is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Update        $update
     * @param PDOStatement  $pdoStatement
     *
     * @return void
     */
    public function afterUpdateRow(
        AbstractTable $table,
        AbstractRow $row,
        Update $update,
        PDOStatement $pdoStatement
    ): void {
    }

    /**
     * Runs before a row specific Delete query is created
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     *
     * @return void
     */
    public function beforeDeleteRow(
        AbstractTable $table,
        AbstractRow $row
    ): void {
    }

    /**
     * Runs before a row specific Insert query is created
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     *
     * @return array|null
     */
    public function beforeInsertRow(
        AbstractTable $table,
        AbstractRow $row
    ): array | null {
        return null;
    }

    /**
     * Runs before a row specific Update query is created
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     *
     * @return array|null
     */
    public function beforeUpdateRow(
        AbstractTable $table,
        AbstractRow $row
    ): array | null {
        return null;
    }

    /**
     * Runs after a Delete query is created
     *
     * @param AbstractTable $table
     * @param Delete        $delete
     *
     * @return void
     */
    public function modifyDelete(AbstractTable $table, Delete $delete): void
    {
    }

    /**
     * Runs after a row specific Delete query is created
     * but before it is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Delete        $delete
     *
     * @return void
     */
    public function modifyDeleteRow(
        AbstractTable $table,
        AbstractRow $row,
        Delete $delete
    ): void {
    }

    /**
     * Runs after any Insert query is created
     *
     * @param AbstractTable $table
     * @param Insert        $insert
     *
     * @return void
     */
    public function modifyInsert(AbstractTable $table, Insert $insert): void
    {
    }

    /**
     * Runs after a row specific Insert query is created
     * but before it is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Insert        $insert
     *
     * @return void
     */
    public function modifyInsertRow(
        AbstractTable $table,
        AbstractRow $row,
        Insert $insert
    ): void {
    }

    /**
     * Runs after a Select query is created
     *
     * @param AbstractTable       $table
     * @param AbstractTableSelect $select
     *
     * @return void
     */
    public function modifySelect(
        AbstractTable $table,
        AbstractTableSelect $select
    ): void {
    }

    /**
     * Runs after a newly selected Row is populated
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     *
     * @return void
     */
    public function modifySelectedRow(
        AbstractTable $table,
        AbstractRow $row
    ): void {
    }

    /**
     * Runs after any Update query is created
     *
     * @param AbstractTable $table
     * @param Update        $update
     *
     * @return void
     */
    public function modifyUpdate(AbstractTable $table, Update $update): void
    {
    }

    /**
     * Runs after a row specific Update query is created
     * but before it is performed
     *
     * @param AbstractTable $table
     * @param AbstractRow   $row
     * @param Update        $update
     *
     * @return void
     */
    public function modifyUpdateRow(
        AbstractTable $table,
        AbstractRow $row,
        Update $update
    ): void {
    }
}
