<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\InvoicesWithEvents;

use PDOStatement;
use Phalcon\DataMapper\Query\Delete;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Update;
use Phalcon\DataMapper\Table\AbstractRow;
use Phalcon\DataMapper\Table\AbstractTable;
use Phalcon\DataMapper\Table\AbstractTableSelect;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesTableEvents;
use Phalcon\Tests\Fixtures\Storage;

use function parent;

class InvoicesWithEventsTableEvents extends InvoicesTableEvents
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
        Storage::set(
            'afterDeleteRow',
            [
                'table'        => $table,
                'row'          => $row,
                'delete'       => $delete,
                'pdoStatement' => $pdoStatement,
            ]
        );

        parent::afterDeleteRow($table, $row, $delete, $pdoStatement);
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
        Storage::set(
            'afterInsertRow',
            [
                'table'        => $table,
                'row'          => $row,
                'insert'       => $insert,
                'pdoStatement' => $pdoStatement,
            ]
        );

        parent::afterInsertRow($table, $row, $insert, $pdoStatement);
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
        Storage::set(
            'afterUpdateRow',
            [
                'table'        => $table,
                'row'          => $row,
                'update'       => $update,
                'pdoStatement' => $pdoStatement,
            ]
        );

        parent::afterUpdateRow($table, $row, $update, $pdoStatement);
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
        Storage::set(
            'beforeDeleteRow',
            [
                'table' => $table,
                'row'   => $row,
            ]
        );

        parent::beforeDeleteRow($table, $row);
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
    ): ?array {
        Storage::set(
            'beforeInsertRow',
            [
                'table' => $table,
                'row'   => $row,
            ]
        );

        return parent::beforeInsertRow($table, $row);
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
    ): ?array {
        Storage::set(
            'beforeUpdateRow',
            [
                'table' => $table,
                'row'   => $row,
            ]
        );

        return parent::beforeUpdateRow($table, $row);
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
        Storage::set(
            'modifyDelete',
            [
                'table'  => $table,
                'delete' => $delete,
            ]
        );

        parent::modifyDelete($table, $delete);
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
        Storage::set(
            'modifyDeleteRow',
            [
                'table'  => $table,
                'row'    => $row,
                'delete' => $delete,
            ]
        );

        parent::modifyDeleteRow($table, $row, $delete);
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
        Storage::set(
            'modifyInsert',
            [
                'table'  => $table,
                'insert' => $insert,
            ]
        );

        parent::modifyInsert($table, $insert);
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
        Storage::set(
            'modifyInsertRow',
            [
                'table'  => $table,
                'row'    => $row,
                'insert' => $insert,
            ]
        );

        parent::modifyInsertRow($table, $row, $insert);
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
        Storage::set(
            'modifySelect',
            [
                'table'  => $table,
                'select' => $select,
            ]
        );

        parent::modifySelect($table, $select);
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
        Storage::set(
            'modifySelectedRow',
            [
                'table' => $table,
                'row'   => $row,
            ]
        );

        parent::modifySelectedRow($table, $row);
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
        Storage::set(
            'modifyUpdate',
            [
                'table'  => $table,
                'update' => $update,
            ]
        );

        parent::modifyUpdate($table, $update);
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
        Storage::set(
            'modifyUpdateRow',
            [
                'table'  => $table,
                'row'    => $row,
                'update' => $update,
            ]
        );

        parent::modifyUpdateRow($table, $row, $update);
    }
}
