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

namespace Phalcon\Db\Dialect\Traits;

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;

use function addcslashes;
use function explode;
use function implode;
use function is_float;
use function is_int;
use function is_string;
use function strtoupper;

trait TextTrait
{
    /**
     * @param string      $tableName
     * @param string|null $schemaName
     *
     * @return string
     */
    protected function alter(string $tableName, string | null $schemaName = null): string
    {
        return 'ALTER TABLE ' . $this->prepareTable($tableName, $schemaName);
    }

    /**
     * @param string $item
     * @param string $object
     * @param string $tableName
     * @param string $schemaName
     *
     * @return string
     */
    protected function alterTableDrop(
        string $object,
        string $item,
        string $tableName,
        string $schemaName
    ): string {
        return $this->alter($tableName, $schemaName)
            . ' DROP ' . $object . ' '
            . $this->delimit($item);
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnComment(Column $column): string
    {
        return empty($column->getComment())
            ? ''
            : ' COMMENT ' . $this->delimit($column->getComment(), "'");
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnFirstAfterPositions(
        Column $column
    ): string {
        $sql = '';
        if (true === $column->isFirst()) {
            $sql = ' FIRST';
        } else {
            $afterPosition = $column->getAfterPosition();

            if (!empty($afterPosition)) {
                $sql = ' AFTER ' . $this->delimit($afterPosition);
            }
        }

        return $sql;
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnHasDefault(Column $column): string
    {
        $sql = '';
        if (true === $column->hasDefault()) {
            $defaultValue = $column->getDefault();

            if (
                (
                    is_string($defaultValue) &&
                    (
                        str_contains(strtoupper($defaultValue), 'CURRENT_TIMESTAMP') ||
                        str_contains(strtoupper($defaultValue), 'NULL')
                    )
                ) ||
                is_int($defaultValue) ||
                is_float($defaultValue)
            ) {
                $sql = ' DEFAULT ' . $defaultValue;
            } else {
                $sql = ' DEFAULT "'
                    . addcslashes($defaultValue, '"')
                    . '"';
            }
        }

        return $sql;
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnIsAutoIncrement(Column $column): string
    {
        return $column->isAutoIncrement() ? ' AUTO_INCREMENT' : '';
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnIsNull(Column $column): string
    {
        return $column->isNotNull() ? ' NOT' : '';
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnIsPrimary(Column $column): string
    {
        return $column->isPrimary() ? ' PRIMARY KEY' : '';
    }

    /**
     * Checks if the size and/or scale are present and encloses those values
     * in parentheses if need be
     *
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnSizeAndScale(Column $column): string
    {
        $columnSql = '';
        if ($column->getSize()) {
            $columnSql .= '(' . $column->getSize();

            if ($column->getScale()) {
                $columnSql .= ',' . $column->getScale();
            }

            $columnSql .= ')';
        }

        return $columnSql;
    }

    /**
     * Checks if a column is unsigned or not and returns the relevant SQL syntax
     *
     * @param Column $column
     *
     * @return string
     */
    protected function checkColumnUnsigned(Column $column): string
    {
        return $column->isUnsigned() ? ' UNSIGNED' : '';
    }

    /**
     * @param Reference $reference
     *
     * @return string
     */
    protected function checkReferenceConstraint(Reference $reference): string
    {
        $sql = '';
        if ($reference->getName()) {
            $sql .= ' CONSTRAINT ' . $this->delimit($reference->getName());
        }

        return $sql;
    }

    /**
     * @param Reference $reference
     *
     * @return string
     */
    protected function checkReferenceOnDelete(Reference $reference): string
    {
        $onDelete = $reference->getOnDelete();

        return empty($onDelete)
            ? ''
            : ' ON DELETE ' . $onDelete;
    }

    /**
     * @param Reference $reference
     *
     * @return string
     */
    protected function checkReferenceOnUpdate(Reference $reference): string
    {
        $onUpdate = $reference->getOnUpdate();

        return empty($onUpdate)
            ? ''
            : ' ON UPDATE ' . $onUpdate;
    }

    /**
     * @param string $identifier
     * @param string $delimiter
     *
     * @return string
     */
    protected function delimit(string $identifier, string $delimiter = '`'): string
    {
        return $delimiter . $identifier . $delimiter;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function drop(string $type): string
    {
        return 'DROP ' . $type . ' ';
    }

    /**
     * @param bool $exists
     *
     * @return string
     */
    protected function exists(bool $exists): string
    {
        return $exists ? 'IF EXISTS ' : '';
    }

    /**
     * @param string      $table
     * @param string      $viewName
     * @param string|null $schemaName
     *
     * @return string
     */
    protected function getExistsSql(
        string $table,
        string $viewName,
        string | null $schemaName
    ): string {
        return 'SELECT IF(COUNT(*) > 0, 1, 0) '
            . 'FROM `INFORMATION_SCHEMA`.' . $this->delimit($table) . ' '
            . 'WHERE `TABLE_NAME` = ' . $this->delimit($viewName, "'") . ' '
            . 'AND `TABLE_SCHEMA` = ' . $this->getMysqlSchemaString($schemaName);
    }

    /**
     * @param string|null $schemaName
     *
     * @return string
     */
    protected function getMysqlSchemaString(string | null $schemaName): string
    {
        return empty($schemaName)
            ? 'DATABASE()' :
            $this->delimit($schemaName, "'");
    }

    /**
     * @return string
     */
    protected function getNullString(): string
    {
        return ' NULL';
    }

    /**
     * @param array $definition
     *
     * @return array
     * @throws Exception
     */
    protected function getTableColumns(array $definition): array
    {
        $result  = [];
        $columns = $definition['columns'];
        foreach ($columns as $column) {
            $result[] = $this->delimit($column->getName())
                . ' '
                . $this->getColumnDefinition($column)
                . $this->checkColumnIsNull($column)
                . $this->getNullString()
                . $this->checkColumnHasDefault($column)
                . $this->checkColumnIsAutoIncrement($column)
                . $this->checkColumnIsPrimary($column)
                . $this->checkColumnComment($column);
        }

        return $result;
    }

    protected function getTableIndexes(array $definition): array
    {
        $result = [];
        /**
         * Create related indexes
         */
        if (isset($definition['indexes'])) {
            $indexes = $definition['indexes'];
            /** @var Index $index */
            foreach ($indexes as $index) {
                $indexName = $index->getName();
                $indexType = $index->getType() ? $index->getType() . ' ' : '';

                /**
                 * If the index name is primary we add a primary key
                 */
                $columnList = $this->wrap($this->getColumnList($index->getColumns()));
                if ($indexName === 'PRIMARY') {
                    $indexSql = 'PRIMARY KEY ' . $columnList;
                } else {
                    $indexSql = $indexType
                        . 'KEY '
                        . $this->delimit($indexName)
                        . ' '
                        . $columnList;
                }

                $result[] = $indexSql;
            }
        }

        return $result;
    }

    /**
     * Generates SQL to add the table creation options
     *
     * @param array $definition
     *
     * @return string
     */
    protected function getTableOptions(array $definition): string
    {
        $tableNameOptions = [];
        $options          = $definition['options'];
        /**
         * Check if there is an ENGINE option
         */
        $engine = $options['ENGINE'] ?? '';
        if (!empty($engine)) {
            $tableNameOptions[] = 'ENGINE=' . $engine;
        }

        /**
         * Check if there is an AUTO_INCREMENT option
         */
        $autoIncrement = $options['AUTO_INCREMENT'] ?? '';
        if (!empty($autoIncrement)) {
            $tableNameOptions[] = 'AUTO_INCREMENT=' . $autoIncrement;
        }

        /**
         * Check if there is a TABLE_COLLATION option
         */
        $tableNameCollation = $options['TABLE_COLLATION'] ?? '';
        if (!empty($tableNameCollation)) {
            $collationParts     = explode('_', $tableNameCollation);
            $tableNameOptions[] = 'DEFAULT CHARSET=' . $collationParts[0];
            $tableNameOptions[] = 'COLLATE=' . $tableNameCollation;
        }

        return implode(' ', $tableNameOptions);
    }

    /**
     * @param array $definition
     *
     * @return array
     * @throws Exception
     */
    protected function getTableReferences(array $definition): array
    {
        $result = [];
        if (isset($definition['references'])) {
            $references = $definition['references'];
            foreach ($references as $reference) {
                $result[] = 'CONSTRAINT '
                    . $this->delimit($reference->getName())
                    . ' FOREIGN KEY '
                    . $this->wrap($this->getColumnList($reference->getColumns()))
                    . ' REFERENCES '
                    . $this->prepareTable($reference->getReferencedTable(), $reference->getReferencedSchema())
                    . ' '
                    . $this->wrap($this->getColumnList($reference->getReferencedColumns()))
                    . $this->checkReferenceOnDelete($reference)
                    . $this->checkReferenceOnUpdate($reference);
            }
        }

        return $result;
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    protected function wrap(string $identifier): string
    {
        return '(' . $identifier . ')';
    }
}
