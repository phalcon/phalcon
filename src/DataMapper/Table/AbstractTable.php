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
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\DataMapper\Query\Delete;
use Phalcon\DataMapper\Query\Insert;
use Phalcon\DataMapper\Query\Update;
use Phalcon\DataMapper\Table\Exception\ImmutableAfterDeletedException;
use Phalcon\DataMapper\Table\Exception\InvalidOptionException;
use Phalcon\DataMapper\Table\Exception\NoPrimaryKeyException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueChangedException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueMissingException;
use Phalcon\DataMapper\Table\Exception\PrimaryValueNotScalarException;
use Phalcon\DataMapper\Table\Exception\PropertyDoesNotExistException;
use Phalcon\DataMapper\Table\Exception\UnexpectedRowCountAffectedException;

abstract class AbstractTable
{
    public const AUTOINC_COLUMN   = null;
    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS       = [];
    public const COLUMN_NAMES  = [];
    public const COMPOSITE_KEY = false;
    public const NAME          = '';
    public const PRIMARY_KEY   = [];
    public const ROW_CLASS     = '';

    public function __construct(
        protected ConnectionLocator $connectionLocator,
        protected AbstractTableEvents $tableEvents
    ) {
    }

    /**
     * Creates a new Delete object
     *
     * @return Delete
     * @throws ConnectionNotFound
     */
    public function delete(): Delete
    {
        $delete = Delete::new($this->getWriteConnection());

        $delete->table($delete->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyDelete($this, $delete);

        return $delete;
    }

    /**
     * Deletes a row from a table
     *
     * @param AbstractRow $row
     *
     * @return PDOStatement|null
     * @throws ConnectionNotFound
     * @throws Exception
     * @throws InvalidOptionException
     * @throws NoPrimaryKeyException
     * @throws PropertyDoesNotExistException
     * @throws UnexpectedRowCountAffectedException
     */
    public function deleteRow(AbstractRow $row): PDOStatement | null
    {
        $delete = $this->deleteRowPrepare($row);

        return $this->deleteRowPerform($row, $delete);
    }

    /**
     * Performs a delete
     *
     * @param AbstractRow $row
     * @param Delete      $delete
     *
     * @return PDOStatement|null
     * @throws Exception
     * @throws InvalidOptionException
     * @throws NoPrimaryKeyException
     * @throws UnexpectedRowCountAffectedException
     */
    public function deleteRowPerform(
        AbstractRow $row,
        Delete $delete
    ): PDOStatement | null {
        if ($row->getLastAction() === $row::DELETE) {
            return null;
        }

        if (empty(static::PRIMARY_KEY)) {
            throw new NoPrimaryKeyException('delete row', static::NAME);
        }

        $pdoStatement = $delete->perform();

        $rowCount = $pdoStatement->rowCount();
        if (1 !== $rowCount) {
            throw new UnexpectedRowCountAffectedException($rowCount);
        }

        $this->tableEvents->afterDeleteRow($this, $row, $delete, $pdoStatement);
        $row->setLastAction($row::DELETE);

        return $pdoStatement;
    }

    /**
     * Prepares the objects for the delete perform
     *
     * @param AbstractRow $row
     *
     * @return Delete
     * @throws ConnectionNotFound
     * @throws PropertyDoesNotExistException
     */
    public function deleteRowPrepare(AbstractRow $row): Delete
    {
        $this->tableEvents->beforeDeleteRow($this, $row);

        $delete = $this->delete();
        foreach (static::PRIMARY_KEY as $primaryColumn) {
            $delete->where($primaryColumn . ' = ', $row->get($primaryColumn));
        }

        $this->tableEvents->modifyDeleteRow($this, $row, $delete);

        return $delete;
    }

    /**
     * Returns a row from the table
     *
     * @param mixed $primaryValue
     *
     * @return AbstractRow|null
     * @throws ConnectionNotFound
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    public function fetchRow(mixed $primaryValue): AbstractRow | null
    {
        return $this->selectRow($this->select(), $primaryValue);
    }

    /**
     * Returns rows from the table
     *
     * @param array $primaryValues
     *
     * @return array
     * @throws ConnectionNotFound
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    public function fetchRows(array $primaryValues): array
    {
        return $this->selectRows($this->select(), $primaryValues);
    }

    /**
     * Returns the database read connection
     *
     * @return Connection
     * @throws ConnectionNotFound
     */
    public function getReadConnection(): Connection
    {
        return $this->connectionLocator->getRead();
    }

    /**
     * Returns the database write connection
     *
     * @return Connection
     * @throws ConnectionNotFound
     */
    public function getWriteConnection(): Connection
    {
        return $this->connectionLocator->getWrite();
    }

    /**
     * Creates a new Insert object
     *
     * @return Insert
     * @throws ConnectionNotFound
     */
    public function insert(): Insert
    {
        $insert = Insert::new($this->getWriteConnection());

        $insert->into($insert->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyInsert($this, $insert);

        return $insert;
    }

    /**
     * Inserts a row from a table
     *
     * @param AbstractRow $row
     *
     * @return PDOStatement
     * @throws ConnectionNotFound
     * @throws Exception
     * @throws ImmutableAfterDeletedException
     * @throws InvalidOptionException
     * @throws PropertyDoesNotExistException
     * @throws UnexpectedRowCountAffectedException
     */
    public function insertRow(AbstractRow $row): PDOStatement
    {
        return $this->insertRowPerform(
            $row,
            $this->insertRowPrepare($row)
        );
    }

    /**
     * Performs an insert
     *
     * @param AbstractRow $row
     * @param Insert      $insert
     *
     * @return PDOStatement
     * @throws Exception
     * @throws ImmutableAfterDeletedException
     * @throws InvalidOptionException
     * @throws PropertyDoesNotExistException
     * @throws UnexpectedRowCountAffectedException
     */
    public function insertRowPerform(
        AbstractRow $row,
        Insert $insert
    ): PDOStatement {
        $pdoStatement = $insert->perform();

        $rowCount = $pdoStatement->rowCount();
        if (1 !== $rowCount) {
            throw new UnexpectedRowCountAffectedException($rowCount);
        }

        /** @var null|string $autoinc */
        $autoinc = static::AUTOINC_COLUMN;
        if ($autoinc !== null) {
            $row->set(
                $autoinc,
                $insert->getLastInsertId(static::AUTOINC_SEQUENCE)
            );
        }

        $this->tableEvents->afterInsertRow($this, $row, $insert, $pdoStatement);

        $row->setLastAction($row::INSERT);

        return $pdoStatement;
    }

    /**
     * Prepares the objects for the insert perform
     *
     * @param AbstractRow $row
     *
     * @return Insert
     * @throws ConnectionNotFound
     */
    public function insertRowPrepare(AbstractRow $row): Insert
    {
        $copy = $this->tableEvents->beforeInsertRow($this, $row);
        if (null === $copy) {
            $copy = $row->getCopy();
        }

        $insert = $this->insert();
        /** @var null|string $autoinc */
        $autoinc = static::AUTOINC_COLUMN;
        if (null !== $autoinc && true !== isset($copy[$autoinc])) {
            unset($copy[$autoinc]);
        }

        $insert->columns($copy);

        $this->tableEvents->modifyInsertRow($this, $row, $insert);

        return $insert;
    }

    /**
     * @param array $columns
     *
     * @return AbstractRow
     */
    public function newRow(array $columns = []): AbstractRow
    {
        $rowClass = static::ROW_CLASS;
        /** @var AbstractRow $row */
        $row = new $rowClass($columns);

        return $row;
    }

    /**
     * @param array $columns
     *
     * @return AbstractRow
     * @throws InvalidOptionException
     */
    public function newSelectedRow(array $columns): AbstractRow
    {
        $row = $this->newRow($columns);
        $this->tableEvents->modifySelectedRow($this, $row);
        $row->setLastAction($row::SELECT);

        return $row;
    }

    /**
     * @param array $whereEquals
     *
     * @return AbstractTableSelect
     * @throws ConnectionNotFound
     */
    public function select(array $whereEquals = []): AbstractTableSelect
    {
        /** @var class-string $class */
        $class  = get_class($this) . 'Select';
        $select = $class::new($this->getReadConnection(), $this, $whereEquals);
        $this->tableEvents->modifySelect($this, $select);

        return $select;
    }

    /**
     * @param AbstractTableSelect $select
     * @param mixed               $primaryValue
     *
     * @return AbstractRow|null
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    public function selectRow(
        AbstractTableSelect $select,
        mixed $primaryValue
    ): AbstractRow | null {
        if (static::COMPOSITE_KEY) {
            return $this->selectRowComposite($select, $primaryValue);
        }

        $column = $select->quoteIdentifier(static::PRIMARY_KEY[0]);
        $select->where($column . ' = ', $primaryValue);

        return $select->fetchRow();
    }

    /**
     * @param AbstractTableSelect $select
     * @param array               $primaryValues
     *
     * @return array
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    public function selectRows(
        AbstractTableSelect $select,
        array $primaryValues
    ): array {
        if (static::COMPOSITE_KEY) {
            return $this->selectRowsComposite($select, $primaryValues);
        }

        $column = $select->quoteIdentifier(static::PRIMARY_KEY[0]);
        $select->where($column . ' IN ', $primaryValues);

        return $select->fetchRows();
    }

    /**
     * @return Update
     * @throws ConnectionNotFound
     */
    public function update(): Update
    {
        $update = Update::new($this->getWriteConnection());

        $update->table($update->quoteIdentifier(static::NAME));
        $this->tableEvents->modifyUpdate($this, $update);

        return $update;
    }

    /**
     * @param AbstractRow $row
     *
     * @return PDOStatement|null
     * @throws Exception
     * @throws InvalidOptionException
     * @throws NoPrimaryKeyException
     * @throws PrimaryValueChangedException
     * @throws PropertyDoesNotExistException
     * @throws UnexpectedRowCountAffectedException
     */
    public function updateRow(AbstractRow $row): PDOStatement | null
    {
        return $this->updateRowPerform(
            $row,
            $this->updateRowPrepare($row)
        );
    }

    /**
     * @param AbstractRow $row
     * @param Update      $update
     *
     * @return PDOStatement|null
     * @throws Exception
     * @throws InvalidOptionException
     * @throws NoPrimaryKeyException
     * @throws UnexpectedRowCountAffectedException
     */
    public function updateRowPerform(
        AbstractRow $row,
        Update $update
    ): PDOStatement | null {
        if (!$update->hasColumns()) {
            return null;
        }

        if (empty(static::PRIMARY_KEY)) {
            throw new NoPrimaryKeyException('update row', static::NAME);
        }

        $pdoStatement = $update->perform();

        $rowCount = $pdoStatement->rowCount();
        if (1 !== $rowCount) {
            throw new UnexpectedRowCountAffectedException($rowCount);
        }

        $this->tableEvents->afterUpdateRow($this, $row, $update, $pdoStatement);

        $row->setLastAction($row::UPDATE);

        return $pdoStatement;
    }

    /**
     * Prepares the objects for the update perform
     *
     * @param AbstractRow $row
     *
     * @return Update
     * @throws ConnectionNotFound
     * @throws PrimaryValueChangedException
     * @throws PropertyDoesNotExistException
     */
    public function updateRowPrepare(AbstractRow $row): Update
    {
        $diff = $this->tableEvents->beforeUpdateRow($this, $row);
        if ($diff === null) {
            $diff = $row->getDiff();
        }

        $update = $this->update();
        $init   = $row->getInit();

        foreach (static::PRIMARY_KEY as $primaryColumn) {
            if (array_key_exists($primaryColumn, $diff)) {
                throw new PrimaryValueChangedException(
                    $primaryColumn,
                    $init[$primaryColumn],
                    $row->get($primaryColumn)
                );
            }

            $update->where(
                $primaryColumn . ' = ',
                $row->get($primaryColumn)
            );
            unset($diff[$primaryColumn]);
        }

        $update->columns($diff);
        $this->tableEvents->modifyUpdateRow($this, $row, $update);

        return $update;
    }

    /**
     * Checks if the primary key has been properly defined
     *
     * @param array  $primaryValue
     * @param string $column
     *
     * @return void
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    protected function assertCompositePart(
        array $primaryValue,
        string $column
    ): void {
        if (true !== isset($primaryValue[$column])) {
            throw new PrimaryValueMissingException($column);
        }

        if (true !== is_scalar($primaryValue[$column])) {
            throw new PrimaryValueNotScalarException(
                $column,
                $primaryValue[$column]
            );
        }
    }

    /**
     * Selects a row with a composite primary key
     *
     * @param AbstractTableSelect $select
     * @param mixed               $primaryValue
     *
     * @return AbstractRow|null
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    protected function selectRowComposite(
        AbstractTableSelect $select,
        mixed $primaryValue
    ): AbstractRow | null {
        $primaryValue = (array)$primaryValue;
        $condition    = [];

        foreach (static::PRIMARY_KEY as $column) {
            $this->assertCompositePart($primaryValue, $column);
            $condition[] = $select->quoteIdentifier($column)
                . ' = '
                . $select->bindInline($primaryValue[$column]);
        }

        $select->where(implode(' AND ', $condition));

        return $select->fetchRow();
    }

    /**
     * Selects rows with a composite primary key
     *
     * @param AbstractTableSelect $select
     * @param array               $primaryValues
     *
     * @return array
     * @throws PrimaryValueMissingException
     * @throws PrimaryValueNotScalarException
     */
    protected function selectRowsComposite(
        AbstractTableSelect $select,
        array $primaryValues
    ): array {
        foreach ($primaryValues as $primaryValue) {
            $condition = [];

            foreach (static::PRIMARY_KEY as $column) {
                $this->assertCompositePart($primaryValue, $column);
                $condition[] = $select->quoteIdentifier($column)
                    . ' = '
                    . $select->bindInline($primaryValue[$column]);
            }

            $select->orWhere(
                '(' . implode(' AND ', $condition) . ')'
            );
        }

        return $select->fetchRows();
    }
}
