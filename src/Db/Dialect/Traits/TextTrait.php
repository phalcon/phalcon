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

use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\ColumnInterface;
use Phalcon\Db\Exception;
use Phalcon\Db\IndexInterface;
use Phalcon\Db\RawValue;
use Phalcon\Db\ReferenceInterface;

use function explode;
use function implode;
use function is_float;
use function is_int;
use function is_string;
use function strtoupper;

/**
 * @phpstan-import-type db_column_list from DbTypes
 * @phpstan-import-type db_columns from DbTypes
 * @phpstan-import-type db_table_definition from DbTypes
 * @phpstan-import-type db_table_options from DbTypes
 */
trait TextTrait
{
    protected function alter(string $tableName, string | null $schemaName = null): string
    {
        return 'ALTER TABLE ' . $this->prepareTable($tableName, $schemaName);
    }

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

    protected function checkColumnComment(ColumnInterface $column): string
    {
        $comment = $column->getComment();

        return empty($comment)
            ? ''
            : " COMMENT '" . $this->escapeStringLiteral($comment) . "'";
    }

    protected function checkColumnFirstAfterPositions(
        ColumnInterface $column
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

    protected function checkColumnHasDefault(ColumnInterface $column): string
    {
        $sql = '';

        if ($column->isGenerated()) {
            return $sql;
        }

        if (true === $column->hasDefault()) {
            $defaultValue = $column->getDefault();

            if ($defaultValue instanceof RawValue) {
                return ' DEFAULT ' . $defaultValue->getValue();
            }

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
                /** @var scalar|null $defaultValue */
                $sql = " DEFAULT '"
                    . $this->escapeStringLiteral((string)$defaultValue)
                    . "'";
            }
        }

        return $sql;
    }

    protected function checkColumnIsAutoIncrement(ColumnInterface $column): string
    {
        if ($column->isGenerated()) {
            return '';
        }

        return $column->isAutoIncrement() ? ' AUTO_INCREMENT' : '';
    }

    /**
     * Emits the GENERATED ALWAYS AS (...) VIRTUAL|STORED clause. Wraps the
     * shared dialect helper for trait users.
     */
    protected function checkColumnIsGenerated(ColumnInterface $column): string
    {
        return $this->getGeneratedClause($column);
    }

    /**
     * Emits the INVISIBLE keyword for MySQL 8.0.23+ invisible columns.
     * Other dialects override this trait helper to return an empty string.
     */
    protected function checkColumnIsInvisible(ColumnInterface $column): string
    {
        return $column->isInvisible() ? ' INVISIBLE' : '';
    }

    protected function checkColumnIsNull(ColumnInterface $column): string
    {
        return $column->isNotNull() ? ' NOT' : '';
    }

    protected function checkColumnIsPrimary(ColumnInterface $column): string
    {
        return $column->isPrimary() ? ' PRIMARY KEY' : '';
    }

    /**
     * Checks if the size and/or scale are present and encloses those values
     * in parentheses if need be
     */
    protected function checkColumnSizeAndScale(ColumnInterface $column): string
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
     */
    protected function checkColumnUnsigned(ColumnInterface $column): string
    {
        return $column->isUnsigned() ? ' UNSIGNED' : '';
    }

    protected function checkReferenceConstraint(ReferenceInterface $reference): string
    {
        $sql = '';
        if ($reference->getName()) {
            $sql .= ' CONSTRAINT ' . $this->delimit($reference->getName());
        }

        return $sql;
    }

    protected function checkReferenceOnDelete(ReferenceInterface $reference): string
    {
        $onDelete = $reference->getOnDelete();

        return empty($onDelete)
            ? ''
            : ' ON DELETE ' . $onDelete;
    }

    protected function checkReferenceOnUpdate(ReferenceInterface $reference): string
    {
        $onUpdate = $reference->getOnUpdate();

        return empty($onUpdate)
            ? ''
            : ' ON UPDATE ' . $onUpdate;
    }

    protected function delimit(string $identifier, string $delimiter = '`'): string
    {
        return $delimiter . $identifier . $delimiter;
    }

    protected function drop(string $type): string
    {
        return 'DROP ' . $type . ' ';
    }

    protected function exists(bool $exists): string
    {
        return $exists ? 'IF EXISTS ' : '';
    }

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

    protected function getMysqlSchemaString(string | null $schemaName): string
    {
        return empty($schemaName)
            ? 'DATABASE()' :
            "'" . $this->escapeStringLiteral($schemaName) . "'";
    }

    protected function getNullString(): string
    {
        return ' NULL';
    }

    /**
     * Returns the list of CONSTRAINT ... CHECK (...) lines for createTable.
     * Uses the dialect's escape character via the shared getCheckClause()
     * helper.
     *
     * @phpstan-param db_table_definition $definition
     *
     * @return list<string>
     */
    protected function getTableChecks(array $definition): array
    {
        if (!isset($definition['checks'])) {
            return [];
        }

        $result = [];
        foreach ($definition['checks'] as $check) {
            $result[] = $this->getCheckClause($check, $this->escapeChar ?? '`');
        }

        return $result;
    }

    /**
     * The caller rejects a definition without a column list, so the shape
     * below is narrower than `db_table_definition`.
     *
     * @phpstan-param array{columns: db_columns} $definition
     *
     * @return list<string>
     *
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
                . $this->checkColumnIsGenerated($column)
                . $this->checkColumnIsNull($column)
                . $this->getNullString()
                . $this->checkColumnIsInvisible($column)
                . $this->checkColumnHasDefault($column)
                . $this->checkColumnIsAutoIncrement($column)
                . $this->checkColumnIsPrimary($column)
                . $this->checkColumnComment($column);
        }

        return $result;
    }

    /**
     * @phpstan-param db_table_definition $definition
     *
     * @return list<string>
     */
    protected function getTableIndexes(array $definition): array
    {
        $result = [];
        /**
         * Create related indexes
         */
        if (isset($definition['indexes'])) {
            $indexes = $definition['indexes'];
            /** @var IndexInterface $index */
            foreach ($indexes as $index) {
                $indexName = $index->getName();
                $indexType = $index->getType() ? $index->getType() . ' ' : '';

                $columnList = $this->wrap($this->getIndexColumnList($index));
                if ($indexName === 'PRIMARY') {
                    $indexSql = 'PRIMARY KEY ' . $columnList;
                } else {
                    $indexSql = $indexType
                        . 'KEY '
                        . $this->delimit($indexName)
                        . ' '
                        . $columnList;
                    if ($index->isInvisible()) {
                        $indexSql .= ' INVISIBLE';
                    }
                }

                $result[] = $indexSql;
            }
        }

        return $result;
    }

    /**
     * Generates SQL to add the table creation options. The caller emits the
     * clause only when the definition carries the options, so the shape
     * below is narrower than `db_table_definition`.
     *
     * @phpstan-param array{options: db_table_options} $definition
     */
    protected function getTableOptions(array $definition): string
    {
        $tableNameOptions = [];
        /** @var db_table_options $options */
        $options = $definition['options'];
        /**
         * Check if there is an ENGINE option
         *
         * @var string $engine
         */
        $engine = $options['ENGINE'] ?? '';
        if (!empty($engine)) {
            $tableNameOptions[] = 'ENGINE=' . $engine;
        }

        /**
         * Check if there is an AUTO_INCREMENT option
         */
        /** @var int|string $autoIncrement */
        $autoIncrement = $options['AUTO_INCREMENT'] ?? '';
        if (!empty($autoIncrement)) {
            $tableNameOptions[] = 'AUTO_INCREMENT=' . $autoIncrement;
        }

        /**
         * Check if there is a TABLE_COLLATION option
         */
        /** @var string $tableNameCollation */
        $tableNameCollation = $options['TABLE_COLLATION'] ?? '';
        if (!empty($tableNameCollation)) {
            $collationParts     = explode('_', $tableNameCollation);
            $tableNameOptions[] = 'DEFAULT CHARSET=' . $collationParts[0];
            $tableNameOptions[] = 'COLLATE=' . $tableNameCollation;
        }

        /**
         * Check if there is a TABLE_COMMENT option
         */
        /** @var string $tableComment */
        $tableComment = $options['TABLE_COMMENT'] ?? '';
        if (!empty($tableComment)) {
            $tableNameOptions[] = "COMMENT='" . str_replace("'", "''", $tableComment) . "'";
        }

        return implode(' ', $tableNameOptions);
    }

    /**
     * @phpstan-param db_table_definition $definition
     *
     * @return list<string>
     *
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

    protected function wrap(string $identifier): string
    {
        return '(' . $identifier . ')';
    }
}
