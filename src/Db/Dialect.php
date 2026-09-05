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

use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\Exceptions\ConflictTargetColumnRequired;
use Phalcon\Db\Exceptions\ConflictUpdateColumnRequired;
use Phalcon\Db\Exceptions\InvalidGroupByExpression;
use Phalcon\Db\Exceptions\InvalidListExpression;
use Phalcon\Db\Exceptions\InvalidOrderByExpression;
use Phalcon\Db\Exceptions\InvalidSqlExpression;
use Phalcon\Db\Exceptions\InvalidSqlExpressionType;
use Phalcon\Db\Exceptions\InvalidUnaryExpression;
use Phalcon\Db\Exceptions\MaterializedViewsNotSupported;
use Phalcon\Db\Exceptions\MissingDefinitionKey;
use Phalcon\Db\Exceptions\ReturningNotSupported;
use Phalcon\Db\Exceptions\UnsupportedOperator;
use Phalcon\Support\Settings;

use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function range;
use function str_replace;
use function strlen;
use function strtoupper;
use function trim;

/**
 * This is the base class to each database dialect. This implements
 * common methods to transform intermediate code into its RDBMS related syntax
 *
 * @phpstan-import-type db_bind_counts from DbTypes
 * @phpstan-import-type db_column_list from DbTypes
 * @phpstan-import-type db_column_names from DbTypes
 * @phpstan-import-type db_custom_functions from DbTypes
 * @phpstan-import-type db_expression from DbTypes
 * @phpstan-import-type db_limit_expression from DbTypes
 * @phpstan-import-type db_joins from DbTypes
 * @phpstan-import-type db_limit_number from DbTypes
 * @phpstan-import-type db_select_definition from DbTypes
 * @phpstan-import-type db_table_name from DbTypes
 * @phpstan-import-type db_view_definition from DbTypes
 */
abstract class Dialect implements DialectInterface
{
    /**
     * @var db_custom_functions
     */
    protected array $customFunctions = [];
    protected string $escapeChar;
    /**
     * Dialect-specific operators that a concrete dialect must opt into via
     * $supportedOperators; using one elsewhere throws.
     *
     * @var list<string>
     */
    protected array $guardedOperators = ["@@", "@>", "<@", "&&", "||", "->", "->>", "#>", "#>>"];
    /**
     * Subset of $guardedOperators that this dialect emits. Overridden per
     * dialect.
     *
     * @var list<string>
     */
    protected array $supportedOperators = [];

    /**
     * Generates SQL to create a materialized view. Supported by PostgreSQL;
     * MySQL and SQLite inherit this throw.
     *
     * @phpstan-param db_view_definition $definition
     *
     * @throws Exception
     */
    public function createMaterializedView(
        string $viewName,
        array $definition,
        ?string $schemaName = null
    ): string {
        throw new MaterializedViewsNotSupported();
    }

    /**
     * Generate SQL to create a new savepoint
     */
    public function createSavepoint(string $name): string
    {
        return "SAVEPOINT " . $name;
    }

    /**
     * Generates SQL to drop a materialized view (PostgreSQL only).
     *
     * @throws Exception
     */
    public function dropMaterializedView(
        string $viewName,
        ?string $schemaName = null,
        bool $ifExists = true
    ): string {
        throw new MaterializedViewsNotSupported();
    }

    /**
     * Escape identifiers
     */
    final public function escape(
        string $input,
        string $escapeChar = ""
    ): string {
        $identifiers = Settings::get("db.escape_identifiers");
        if (true !== $identifiers) {
            return $input;
        }

        $escapeChar = (!empty($escapeChar)) ? $escapeChar : $this->escapeChar;
        if (true !== str_contains($input, ".")) {
            if ("" !== $escapeChar && "*" !== $input) {
                return $escapeChar
                    . str_replace($escapeChar, $escapeChar . $escapeChar, $input)
                    . $escapeChar;
            }

            return $input;
        }

        $parts    = explode(".", trim($input, $escapeChar));
        $newParts = $parts;
        foreach ($parts as $key => $part) {
            if ("" === $escapeChar || "" === $part || "*" === $part) {
                continue;
            }

            $newParts[$key] = $escapeChar
                . str_replace($escapeChar, $escapeChar . $escapeChar, $part)
                . $escapeChar;
        }

        return implode(".", $newParts);
    }

    /**
     * Escape Schema
     */
    final public function escapeSchema(
        string $input,
        string $escapeChar = ""
    ): string {
        $identifiers = Settings::get("db.escape_identifiers");
        if (true !== $identifiers) {
            return $input;
        }
        $escapeChar = (!empty($escapeChar)) ? $escapeChar : $this->escapeChar;

        return $escapeChar . trim($input, $escapeChar) . $escapeChar;
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause. The optional
     * `modifier` appends a row-lock disposition keyword.
     *
     *```php
     * $sql = $dialect->forUpdate("SELECT * FROM co_invoices");
     * echo $sql; // SELECT * FROM co_invoices FOR UPDATE
     *
     * $sql = $dialect->forUpdate(
     *     "SELECT * FROM co_invoices",
     *     Dialect::LOCK_NOWAIT
     * );
     * echo $sql; // SELECT * FROM co_invoices FOR UPDATE NOWAIT
     *```
     */
    public function forUpdate(string $sqlQuery, string $modifier = ''): string
    {
        if ($modifier !== '') {
            return $sqlQuery . ' FOR UPDATE ' . $modifier;
        }

        return $sqlQuery . ' FOR UPDATE';
    }

    /**
     * Gets a list of columns with escaped identifiers
     *
     * ```php
     * echo $dialect->getColumnList(
     *     [
     *         "column1",
     *         "column",
     *     ]
     * );
     * ```
     *
     * @phpstan-param db_column_list $columnList
     * @phpstan-param db_bind_counts $bindCounts
     *
     * @throws Exception
     */
    final public function getColumnList(
        array $columnList,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $columns = [];

        foreach ($columnList as $column) {
            $columns[] = $this->getSqlColumn($column, $escapeChar, $bindCounts);
        }

        return implode(", ", $columns);
    }

    /**
     * Returns registered functions
     *
     * @phpstan-return db_custom_functions
     */
    public function getCustomFunctions(): array
    {
        return $this->customFunctions;
    }

    /**
     * Resolve Column expressions
     *
     * @phpstan-param db_expression  $column
     * @phpstan-param db_bind_counts $bindCounts
     *
     * @throws Exception
     */
    final public function getSqlColumn(
        array | string $column,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (is_string($column)) {
            return $this->prepareQualified($column, "", $escapeChar);
        }

        $columnExpression = $column;
        if (!isset($column["type"])) {
            /**
             * The index "0" is the column field
             */
            $columnField = $column[0];
            if (is_array($columnField)) {
                $columnExpression = [
                    "type"  => "scalar",
                    "value" => $columnField,
                ];
            } elseif ($columnField === "*") {
                $columnExpression = [
                    "type" => "all",
                ];
            } else {
                $columnExpression = [
                    "type" => "qualified",
                    "name" => $columnField,
                ];
            }

            /**
             * The index "1" is the domain column
             */
            if (isset($column[1]) && "" !== $column[1]) {
                $columnExpression["domain"] = $column[1];
            }

            /**
             * The index "2" is the column alias
             */
            if (isset($column[2]) && $column[2]) {
                $columnExpression["sqlAlias"] = $column[2];
            }
        }

        /**
         * Resolve column expressions
         */
        $column = $this->getSqlExpression(
            $columnExpression,
            $escapeChar,
            $bindCounts
        );

        /**
         * Escape alias and concatenate to value SQL
         */
        if (isset($columnExpression["sqlAlias"]) || isset($columnExpression["alias"])) {
            /** @var string|null $columnAlias */
            $columnAlias = $columnExpression["sqlAlias"] ?? null;
            /** @var string $columnAlias */
            $columnAlias = (null === $columnAlias) ? $columnExpression["alias"] : $columnAlias;

            return $this->prepareColumnAlias($column, $columnAlias, $escapeChar);
        }

        return $this->prepareColumnAlias($column, "", $escapeChar);
    }

    /**
     * Transforms an intermediate representation for an expression into a
     * database system valid expression
     *
     * @phpstan-param db_expression  $expression
     * @phpstan-param db_bind_counts $bindCounts
     *
     * @throws Exception
     */
    public function getSqlExpression(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (!isset($expression["type"])) {
            throw new InvalidSqlExpression();
        }

        /** @var string $type */
        $type = $expression["type"];
        switch ($type) {
            /**
             * Resolve scalar column expressions
             */
            case "scalar":
                return $this->getSqlExpressionScalar(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve object expressions
             */
            case "object":
                return $this->getSqlExpressionObject(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve qualified expressions
             */
            case "qualified":
                return $this->getSqlExpressionQualified($expression, $escapeChar);

            /**
             * Resolve literal OR placeholder expressions
             */
            case "literal":
                /** @var string $literal */
                $literal = $expression["value"];

                if (isset($expression["escape"]) && $expression["escape"]) {
                    return "'" . $this->escapeStringLiteral($literal) . "'";
                }

                return $literal;

            case "placeholder":
                /** @var string $value */
                $value = $expression["value"];

                if (isset($expression["times"])) {
                    $placeholders = [];
                    /** @var string $rawValue */
                    $rawValue = $expression["rawValue"];
                    /** @var int $times */
                    $times = $expression["times"];

                    if (isset($bindCounts[$rawValue])) {
                        $times = $bindCounts[$rawValue];
                    }

                    foreach (range(1, $times) as $counter) {
                        $placeholders[] = $value . ($counter - 1);
                    }

                    return implode(", ", $placeholders);
                }

                return $value;

            /**
             * Resolve binary operations expressions
             */
            case "binary-op":
                return $this->getSqlExpressionBinaryOperations(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve unary operations expressions
             */
            case "unary-op":
                return $this->getSqlExpressionUnaryOperations(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve parentheses
             */
            case "parentheses":
                /** @var db_expression $left */
                $left = $expression["left"];

                return "("
                    . $this->getSqlExpression($left, $escapeChar, $bindCounts)
                    . ")";

            /**
             * Resolve function calls
             */
            case "functionCall":
                return $this->getSqlExpressionFunctionCall(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve lists
             */
            case "list":
                return $this->getSqlExpressionList(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve *
             */
            case "all":
                return $this->getSqlExpressionAll($expression, $escapeChar);

            /**
             * Resolve SELECT
             *
             * Propagate the outer bindCounts into the nested SELECT
             * definition so that array placeholders inside a sub-select
             * are re-expanded against the current bind values instead of
             * the parse-time `times` baked into the cached irPhql. The
             * local copy avoids mutating the cached intermediate. See
             * issue #17004.
             */
            case "select":
                /** @var db_select_definition $nestedDefinition */
                $nestedDefinition = $expression["value"];
                if (!empty($bindCounts)) {
                    $nestedDefinition["bindCounts"] = $bindCounts;
                }
                return "(" . $this->select($nestedDefinition) . ")";

            /**
             * Resolve CAST of values
             */
            case "cast":
                return $this->getSqlExpressionCastValue(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve CONVERT of values encodings
             */
            case "convert":
                return $this->getSqlExpressionConvertValue(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            case "case":
                return $this->getSqlExpressionCase(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );
        }

        /**
         * Expression type wasn't found
         */
        throw new InvalidSqlExpressionType($type);
    }

    /**
     * Transform an intermediate representation of a schema/table into a
     * database system valid expression
     *
     * @phpstan-param db_table_name $tableName
     */
    final public function getSqlTable(
        array | string $tableName,
        string $escapeChar = ""
    ): string {
        if (is_array($tableName)) {
            /**
             * The index "0" is the table name
             * The index "1" is the schema name
             * The index "2" is the table alias
             */

            return $this->prepareTable(
                $tableName[0],
                $tableName[1] ?? null,
                $tableName[2] ?? '',
                $escapeChar
            );
        }

        return $this->escape($tableName, $escapeChar);
    }

    /**
     * Generates the SQL for LIMIT clause
     *
     * ```php
     * // SELECT * FROM co_invoices LIMIT 10
     * echo $dialect->limit(
     *     "SELECT * FROM co_invoices",
     *     10
     * );
     *
     * // SELECT * FROM co_invoices LIMIT 10 OFFSET 50
     * echo $dialect->limit(
     *     "SELECT * FROM co_invoices",
     *     [10, 50]
     * );
     * ```
     *
     * @phpstan-param db_limit_number $number
     */
    public function limit(string $sqlQuery, mixed $number): string
    {
        /**
         * A bound placeholder (":name" / "?N") is emitted unchanged so it can
         * be bound by PDO; any other value is coerced to an integer, so a
         * request-derived pagination value reaching this low-level API cannot
         * inject SQL.
         */
        if (is_array($number)) {
            $sqlQuery .= " LIMIT " . $this->getLimitValue($number[0]);

            /** @var scalar|null $offset */
            $offset = $number[1] ?? null;

            if (null !== $offset && strlen((string) $offset)) {
                $sqlQuery .= " OFFSET " . $this->getLimitValue($offset);
            }

            return $sqlQuery;
        }

        return $sqlQuery . " LIMIT " . $this->getLimitValue($number);
    }

    /**
     * Appends an `ON CONFLICT (col, ...) DO UPDATE SET col = excluded.col`
     * upsert clause to the supplied INSERT statement. Supported by
     * PostgreSQL 9.5+ and SQLite 3.24+. MySQL overrides this method to
     * throw.
     *
     * @phpstan-param db_column_names $conflictColumns
     * @phpstan-param db_column_names $updateColumns
     *
     * @throws Exception
     */
    public function onConflictUpdate(
        string $sqlQuery,
        array $conflictColumns,
        array $updateColumns
    ): string {
        if (empty($conflictColumns)) {
            throw new ConflictTargetColumnRequired();
        }

        if (empty($updateColumns)) {
            throw new ConflictUpdateColumnRequired();
        }

        $assignments = [];
        foreach ($updateColumns as $col) {
            $escaped       = $this->escape((string) $col);
            $assignments[] = $escaped . ' = excluded.' . $escaped;
        }

        /** @var db_column_list $conflictColumns */
        return $sqlQuery
            . ' ON CONFLICT (' . $this->getColumnList($conflictColumns) . ')'
            . ' DO UPDATE SET ' . implode(', ', $assignments);
    }

    /**
     * Generates SQL to refresh a materialized view (PostgreSQL only).
     *
     * @throws Exception
     */
    public function refreshMaterializedView(
        string $viewName,
        ?string $schemaName = null,
        bool $concurrent = false
    ): string {
        throw new MaterializedViewsNotSupported();
    }

    /**
     * Registers custom SQL functions
     *
     * @return $this
     */
    public function registerCustomFunction(
        string $name,
        callable $customFunction
    ): static {
        $this->customFunctions[$name] = $customFunction;

        return $this;
    }

    /**
     * Generate SQL to release a savepoint
     */
    public function releaseSavepoint(string $name): string
    {
        return "RELEASE SAVEPOINT " . $name;
    }

    /**
     * Returns a SQL statement extended with a `RETURNING` clause.
     * Supported by PostgreSQL and SQLite 3.35+; MySQL inherits the throw.
     *
     * @phpstan-param db_column_names $columns
     *
     * @throws Exception
     */
    public function returning(string $sqlQuery, array $columns): string
    {
        throw new ReturningNotSupported();
    }

    /**
     * Generate SQL to rollback a savepoint
     */
    public function rollbackSavepoint(string $name): string
    {
        return "ROLLBACK TO SAVEPOINT " . $name;
    }

    /**
     * Builds a SELECT statement
     *
     * @phpstan-param db_select_definition $definition
     *
     * @throws Exception
     */
    public function select(array $definition): string
    {
        if (!isset($definition["tables"])) {
            throw new MissingDefinitionKey("tables");
        }

        if (!isset($definition["columns"])) {
            throw new MissingDefinitionKey("columns");
        }

        /** @var db_bind_counts $bindCounts */
        $bindCounts = $definition["bindCounts"] ?? [];
        /** @var db_column_list $columns */
        $columns = $definition["columns"];
        /** @var array<array-key, db_table_name>|string $tableNames */
        $tableNames = $definition["tables"];
        $escapeChar = $this->escapeChar;

        $sql = "SELECT";
        if (isset($definition["distinct"])) {
            $sql .= (empty($definition["distinct"])) ? " ALL" : " DISTINCT";
        }

        /**
         * Resolve COLUMNS
         */
        $sql .= " " . $this->getColumnList($columns, $escapeChar, $bindCounts);

        /**
         * Resolve FROM
         */
        $sql .= " " . $this->getSqlExpressionFrom($tableNames, $escapeChar);

        /**
         * Resolve JOINs
         */
        if (
            isset($definition["joins"]) &&
            !empty($definition["joins"])
        ) {
            /** @var db_joins $joins */
            $joins = $definition["joins"];

            $sql .= " "
                . $this->getSqlExpressionJoins($joins, $escapeChar, $bindCounts);
        }

        /**
         * Resolve WHERE
         */
        if (
            isset($definition["where"]) &&
            !empty($definition["where"])
        ) {
            /** @var db_expression|string $where */
            $where = $definition["where"];

            $sql .= " "
                . $this->getSqlExpressionWhere($where, $escapeChar, $bindCounts);
        }

        /**
         * Resolve GROUP BY
         */
        if (
            isset($definition["group"]) &&
            !empty($definition["group"])
        ) {
            /** @var array<array-key, db_expression>|string $group */
            $group = $definition["group"];

            $sql .= " " . $this->getSqlExpressionGroupBy($group, $escapeChar);
        }

        /**
         * Resolve HAVING
         */
        if (
            isset($definition["having"]) &&
            !empty($definition["having"])
        ) {
            /** @var db_expression $having */
            $having = $definition["having"];

            $sql .= " "
                . $this->getSqlExpressionHaving($having, $escapeChar, $bindCounts);
        }

        /**
         * Resolve ORDER BY
         */
        if (
            isset($definition["order"]) &&
            !empty($definition["order"])
        ) {
            /** @var array<array-key, db_expression>|string $order */
            $order = $definition["order"];

            $sql .= " "
                . $this->getSqlExpressionOrderBy($order, $escapeChar, $bindCounts);
        }

        /**
         * Resolve LIMIT
         */
        if (
            isset($definition["limit"]) &&
            !empty($definition["limit"])
        ) {
            $sql = $this->getSqlExpressionLimit(
                [
                    "sql"   => $sql,
                    "value" => $definition["limit"],
                ],
                $escapeChar,
                $bindCounts
            );
        }

        /**
         * Resolve FOR UPDATE
         */
        if (
            isset($definition["forUpdate"]) &&
            !empty($definition["forUpdate"])
        ) {
            $sql .= " FOR UPDATE";
        }

        return $sql;
    }

    /**
     * Checks whether the platform supports the full `ALTER TABLE` matrix:
     * modifying existing columns and adding or dropping foreign keys, primary
     * keys, and check constraints. SQLite returns false - those operations
     * throw a dedicated `Sqlite*NotSupported` exception there (basic
     * `ADD COLUMN` remains available).
     */
    public function supportsAlterTable(): bool
    {
        return true;
    }

    /**
     * Checks whether the platform supports materialized views. Only PostgreSQL
     * returns true; `createMaterializedView()` throws on the other dialects.
     */
    public function supportsMaterializedViews(): bool
    {
        return false;
    }

    /**
     * Checks whether the platform supports the `ON CONFLICT (...) DO UPDATE`
     * upsert clause. MySQL returns false; `onConflictUpdate()` throws there.
     */
    public function supportsOnConflictUpdate(): bool
    {
        return true;
    }

    /**
     * Checks whether the platform supports releasing savepoints.
     */
    public function supportsReleaseSavepoints(): bool
    {
        return $this->supportsSavePoints();
    }

    /**
     * Checks whether the platform supports the `RETURNING` clause. MySQL
     * returns false; `returning()` throws there.
     */
    public function supportsReturning(): bool
    {
        return false;
    }

    /**
     * Checks whether the platform supports savepoints
     */
    public function supportsSavepoints(): bool
    {
        return true;
    }

    /**
     * Checks the column type and if not string it returns the type reference
     *
     * @todo this always returns the type beceuse type is never string
     */
    protected function checkColumnType(ColumnInterface $column): int
    {
        if (is_string($column->getType())) {
            return $column->getTypeReference();
        }

        return $column->getType();
    }

    /**
     * Checks the column type and returns the updated SQL statement
     *
     * @todo check this one also
     */
    protected function checkColumnTypeSql(ColumnInterface $column): string
    {
        if (!is_string($column->getType())) {
            return "";
        }

        return $column->getType();
    }

    /**
     * Escape a string literal for a single quoted SQL string. The standard
     * way doubles the single quotes. A dialect where the backslash is an
     * escape character must override this method.
     */
    protected function escapeStringLiteral(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    /**
     * Builds a CHECK constraint clause from a `CheckInterface`, using the
     * provided escape character for the constraint name.
     */
    protected function getCheckClause(
        CheckInterface $check,
        string $escapeChar = '`'
    ): string {
        $name   = $check->getName();
        $clause = '';

        if ($name !== '') {
            $clause = 'CONSTRAINT ' . $escapeChar . $name . $escapeChar . ' ';
        }

        return $clause . 'CHECK (' . $check->getExpression() . ')';
    }

    /**
     * Returns the size of the column enclosed in parentheses
     */
    protected function getColumnSize(ColumnInterface $column): string
    {
        return "(" . $column->getSize() . ")";
    }

    /**
     * Returns the column size and scale enclosed in parentheses
     */
    protected function getColumnSizeAndScale(ColumnInterface $column): string
    {
        return "(" . $column->getSize() . "," . $column->getScale() . ")";
    }

    /**
     * Builds the `GENERATED ALWAYS AS (<expr>) VIRTUAL|STORED` clause for a
     * generated/computed column. Returns an empty string when the column is
     * not generated. When `forceStored` is `true` the clause is always
     * emitted as `STORED` (PostgreSQL uses this).
     */
    protected function getGeneratedClause(
        ColumnInterface $column,
        bool $forceStored = false
    ): string {
        if (!$column->isGenerated()) {
            return '';
        }

        $storage = ($forceStored || $column->isGenerationStored())
            ? 'STORED'
            : 'VIRTUAL';

        return ' GENERATED ALWAYS AS (' . $column->getGenerationExpression()
            . ') ' . $storage;
    }

    /**
     * Builds the per-index parenthesized column list, honoring per-column
     * sort directions and `RawValue` expression entries.
     */
    protected function getIndexColumnList(
        IndexInterface $index,
        bool $wrapExpressions = true
    ): string {
        $columns       = $index->getColumns();
        $directions    = $index->getDirections();
        $hasExpression = false;

        foreach ($columns as $column) {
            if ($column instanceof RawValue) {
                $hasExpression = true;
                break;
            }
        }

        if (!$hasExpression && empty($directions)) {
            /** @var db_column_list $columns */
            return $this->getColumnList($columns);
        }

        $parts = [];
        $i     = 0;

        foreach ($columns as $column) {
            if ($column instanceof RawValue) {
                $rendered = $wrapExpressions
                    ? '(' . $column->getValue() . ')'
                    : $column->getValue();
            } else {
                $rendered = $this->escape($column);
            }

            if (!empty($directions)) {
                $direction = isset($directions[$i])
                    ? strtoupper((string) $directions[$i])
                    : 'ASC';
                $rendered .= ($direction === 'DESC') ? ' DESC' : ' ASC';
            }

            $parts[] = $rendered;
            $i++;
        }

        return implode(', ', $parts);
    }

    /**
     * Renders a LIMIT/OFFSET value: a bound placeholder passes through, any
     * other value is coerced to an integer to prevent SQL injection.
     */
    protected function getLimitValue(mixed $value): string
    {
        if (
            is_string($value) &&
            (substr($value, 0, 1) === ":" || substr($value, 0, 1) === "?")
        ) {
            return $value;
        }

        /** @var scalar|null $value */

        return (string) (int) $value;
    }

    /**
     * Resolve *
     *
     * @phpstan-param db_expression $expression
     */
    final protected function getSqlExpressionAll(
        array $expression,
        string $escapeChar = ""
    ): string {
        /** @var string $domain */
        $domain = $expression["domain"] ?? '';

        return $this->prepareQualified(
            "*",
            $domain,
            $escapeChar
        );
    }

    /**
     * Resolve binary operations expressions
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionBinaryOperations(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /** @var string $operator */
        $operator = $expression["op"];

        if (
            in_array($operator, $this->guardedOperators) &&
            !in_array($operator, $this->supportedOperators)
        ) {
            throw new UnsupportedOperator($operator);
        }

        /** @var db_expression $leftNode */
        $leftNode = $expression["left"];
        /** @var db_expression $rightNode */
        $rightNode = $expression["right"];

        $left  = $this->getSqlExpression($leftNode, $escapeChar, $bindCounts);
        $right = $this->getSqlExpression($rightNode, $escapeChar, $bindCounts);

        return $left . " " . $operator . " " . $right;
    }

    /**
     * Resolve CASE expressions
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionCase(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /** @var db_expression $caseExpr */
        $caseExpr = $expression["expr"];
        /** @var array<array-key, db_expression> $whenClauses */
        $whenClauses = $expression["when-clauses"];

        $sql = "CASE " . $this->getSqlExpression($caseExpr, $escapeChar, $bindCounts);

        foreach ($whenClauses as $whenClause) {
            /** @var db_expression $whenExpr */
            $whenExpr = $whenClause["expr"];

            if ("when" === $whenClause["type"]) {
                /** @var db_expression $whenThen */
                $whenThen = $whenClause["then"];

                $sql .= " WHEN "
                    . $this->getSqlExpression($whenExpr, $escapeChar, $bindCounts)
                    . " THEN "
                    . $this->getSqlExpression($whenThen, $escapeChar, $bindCounts);
            } else {
                $sql .= " ELSE "
                    . $this->getSqlExpression($whenExpr, $escapeChar, $bindCounts);
            }
        }

        return $sql . " END";
    }

    /**
     * Resolve CAST of values
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionCastValue(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /** @var db_expression $leftNode */
        $leftNode = $expression["left"];
        /** @var db_expression $rightNode */
        $rightNode = $expression["right"];

        $left  = $this->getSqlExpression($leftNode, $escapeChar, $bindCounts);
        $right = $this->getSqlExpression($rightNode, $escapeChar, $bindCounts);

        return "CAST(" . $left . " AS " . $right . ")";
    }

    /**
     * Resolve CONVERT of values encodings
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionConvertValue(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /** @var db_expression $leftNode */
        $leftNode = $expression["left"];
        /** @var db_expression $rightNode */
        $rightNode = $expression["right"];

        $left  = $this->getSqlExpression($leftNode, $escapeChar, $bindCounts);
        $right = $this->getSqlExpression($rightNode, $escapeChar, $bindCounts);

        return "CONVERT(" . $left . " USING " . $right . ")";
    }

    /**
     * Resolve a FROM clause
     *
     * @phpstan-param array<array-key, db_table_name>|string $expression
     */
    final protected function getSqlExpressionFrom(
        array | string $expression,
        string $escapeChar = ""
    ): string {
        if (is_array($expression)) {
            $tables = [];

            foreach ($expression as $tableName) {
                $tables[] = $this->getSqlTable($tableName, $escapeChar);
            }

            return "FROM " . implode(", ", $tables);
        }

        return "FROM " . $expression;
    }

    /**
     * Resolve function calls
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionFunctionCall(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /** @var string $name */
        $name = $expression["name"];

        if (isset($this->customFunctions[$name])) {
            $customFunction = $this->customFunctions[$name];

            /** @var string $rendered */
            $rendered = $customFunction($this, $expression, $escapeChar);

            return $rendered;
        }

        if (
            isset($expression["arguments"]) &&
            is_array($expression["arguments"])
        ) {
            $arguments = $this->getSqlExpression(
                [
                    "type"        => "list",
                    "parentheses" => false,
                    "value"       => $expression["arguments"],
                ],
                $escapeChar,
                $bindCounts
            );

            if (isset($expression["distinct"]) && $expression["distinct"]) {
                return $name . "(DISTINCT " . $arguments . ")";
            }

            return $name . "(" . $arguments . ")";
        }

        return $name . "()";
    }

    /**
     * Resolve a GROUP BY clause
     *
     * @throws Exception
     *
     * @phpstan-param array<array-key, mixed>|string $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionGroupBy(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (is_array($expression)) {
            $fields = [];
            foreach ($expression as $field) {
                if (!is_array($field)) {
                    throw new InvalidGroupByExpression();
                }

                $fields[] = $this->getSqlExpression(
                    $field,
                    $escapeChar,
                    $bindCounts
                );
            }

            return "GROUP BY " . implode(", ", $fields);
        }

        return "GROUP BY " . $expression;
    }

    /**
     * Resolve a HAVING clause
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionHaving(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        return "HAVING " . $this->getSqlExpression($expression, $escapeChar, $bindCounts);
    }

    /**
     * Resolve a JOINs clause
     *
     * @todo Isn't expression just an array?
     *
     * @throws Exception
     *
     * @phpstan-param db_joins $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionJoins(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $sql = "";

        /** @var db_joins $joins */
        $joins = $expression;

        foreach ($joins as $join) {
            /**
             * Check if the join has conditions
             */
            $joinCondition = "1";
            $joinType      = "";
            if (
                isset($join["conditions"]) &&
                !empty($join["conditions"])
            ) {
                /** @var db_expression $joinConditionsArray */
                $joinConditionsArray = $join["conditions"];
                if (!isset($joinConditionsArray[0])) {
                    $joinCondition = $this->getSqlExpression(
                        $joinConditionsArray,
                        $escapeChar,
                        $bindCounts
                    );
                } else {
                    $conditions = [];
                    foreach ($joinConditionsArray as $condition) {
                        /** @var db_expression $condition */
                        $conditions[] = $this->getSqlExpression(
                            $condition,
                            $escapeChar,
                            $bindCounts
                        );
                    }

                    $joinCondition = implode(" AND ", $conditions);
                }
            }

            if (isset($join["type"]) && $join["type"]) {
                /** @var string $type */
                $type     = $join["type"];
                $joinType = $type . " ";
            }

            /** @var db_table_name $joinSource */
            $joinSource = $join["source"];
            $joinTable  = $this->getSqlTable($joinSource, $escapeChar);

            $sql .= " " . $joinType
                . "JOIN " . $joinTable
                . " ON " . $joinCondition;
        }

        return $sql;
    }

    /**
     * Resolve a LIMIT clause
     *
     * @throws Exception
     *
     * @phpstan-param db_limit_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionLimit(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $sql    = "";
        $offset = null;

        /** @var db_limit_expression $limitExpression */
        $limitExpression = $expression;

        $value = $limitExpression["value"];

        if (isset($limitExpression["sql"])) {
            /** @var string $sql */
            $sql = $limitExpression["sql"];
        }

        $limit = $value;
        if (is_array($value)) {
            $limit = $value["number"];
            if (is_array($value["number"])) {
                $limit = $this->getSqlExpression(
                    $value["number"],
                    $escapeChar,
                    $bindCounts
                );
            }

            /**
             * Check for an OFFSET condition
             */
            if (isset($value["offset"]) && is_array($value["offset"])) {
                $offset = $this->getSqlExpression(
                    $value["offset"],
                    $escapeChar,
                    $bindCounts
                );
            }
        }

        return $this->limit($sql, [$limit, $offset]);
    }

    /**
     * Resolve Lists
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionList(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $items     = [];
        $separator = ", ";

        if (isset($expression["separator"])) {
            /** @var string $separator */
            $separator = $expression["separator"];
        }

        if (
            isset($expression[0]) ||
            (isset($expression["value"]) && is_array($expression["value"]))
        ) {
            $values = $expression[0] ?? null;
            /** @var array<array-key, db_expression> $values */
            $values = (null === $values) ? $expression["value"] : $values;

            foreach ($values as $item) {
                $items[] = $this->getSqlExpression($item, $escapeChar, $bindCounts);
            }

            if (
                isset($expression["parentheses"]) &&
                false === $expression["parentheses"]
            ) {
                return implode($separator, $items);
            }

            return "(" . implode($separator, $items) . ")";
        }

        throw new InvalidListExpression();
    }

    /**
     * Resolve object expressions
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionObject(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $objectExpression = [
            "type" => "all",
        ];

        if (
            isset($expression["column"]) ||
            (isset($expression["domain"]) && "" !== $expression["domain"])
        ) {
            $domain = $expression["column"] ?? null;
            $domain = (null !== $domain) ? $expression["domain"] : $domain;

            $objectExpression["domain"] = $domain;
        }

        return $this->getSqlExpression(
            $objectExpression,
            $escapeChar,
            $bindCounts
        );
    }

    /**
     * Resolve an ORDER BY clause
     *
     * @throws Exception
     *
     * @phpstan-param array<array-key, mixed>|string $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionOrderBy(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (is_array($expression)) {
            $fields = [];
            foreach ($expression as $field) {
                if (!is_array($field)) {
                    throw new InvalidOrderByExpression();
                }

                /** @var db_expression $fieldExpression */
                $fieldExpression = $field[0];
                $fieldSql        = $this->getSqlExpression(
                    $fieldExpression,
                    $escapeChar,
                    $bindCounts
                );

                /**
                 * In the numeric 1 position could be ASC/DESC clause
                 */
                if (isset($field[1]) && "" !== $field[1]) {
                    /** @var string $direction */
                    $direction = $field[1];
                    $fieldSql .= " " . $direction;
                }

                $fields[] = $fieldSql;
            }

            return "ORDER BY " . implode(", ", $fields);
        }

        return "ORDER BY " . $expression;
    }

    /**
     * Resolve qualified expressions
     *
     * @phpstan-param db_expression $expression
     */
    final protected function getSqlExpressionQualified(
        array $expression,
        string $escapeChar = ""
    ): string {
        /** @var string $column */
        $column = $expression["name"];

        /**
         * A domain could be a table/schema
         *
         * @var string $domain
         */
        $domain = $expression["domain"] ?? "";

        return $this->prepareQualified($column, $domain, $escapeChar);
    }

    /**
     * Resolve Column expressions
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionScalar(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (isset($expression["column"])) {
            /** @var db_expression|string $column */
            $column = $expression["column"];

            return $this->getSqlColumn($column);
        }

        if (!isset($expression["value"])) {
            throw new InvalidSqlExpression();
        }

        /** @var db_expression|string $value */
        $value = $expression["value"];
        if (is_array($value)) {
            return $this->getSqlExpression($value, $escapeChar, $bindCounts);
        }

        return $value;
    }

    /**
     * Resolve unary operations expressions
     *
     * @throws Exception
     *
     * @phpstan-param db_expression $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionUnaryOperations(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /**
         * Some unary operators use the left operand...
         */
        /** @var string $operator */
        $operator = $expression["op"];

        if (isset($expression["left"])) {
            /** @var db_expression $leftNode */
            $leftNode = $expression["left"];

            return $this->getSqlExpression(
                $leftNode,
                $escapeChar,
                $bindCounts
            ) . " " . $operator;
        }

        /**
         * ...Others use the right operand
         */
        if (isset($expression["right"])) {
            /** @var db_expression $rightNode */
            $rightNode = $expression["right"];

            return $operator . " "
                . $this->getSqlExpression(
                    $rightNode,
                    $escapeChar,
                    $bindCounts
                );
        }

        throw new InvalidUnaryExpression();
    }

    /**
     * Resolve a WHERE clause
     *
     * @throws Exception
     *
     * @phpstan-param db_expression|string $expression
     * @phpstan-param db_bind_counts $bindCounts
     */
    final protected function getSqlExpressionWhere(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (is_array($expression)) {
            return "WHERE " . $this->getSqlExpression(
                $expression,
                $escapeChar,
                $bindCounts
            );
        }

        return "WHERE " . $expression;
    }

    /**
     * Prepares column for this RDBMS
     */
    protected function prepareColumnAlias(
        string $qualified,
        string $alias = "",
        string $escapeChar = ""
    ): string {
        if (!empty($alias)) {
            return $qualified . " AS " . $this->escape($alias, $escapeChar);
        }

        return $qualified;
    }

    /**
     * Prepares qualified for this RDBMS
     */
    protected function prepareQualified(
        string $column,
        string $domain = "",
        string $escapeChar = ""
    ): string {
        if ("" !== $domain) {
            return $this->escape($domain . "." . $column, $escapeChar);
        }

        return $this->escape($column, $escapeChar);
    }

    /**
     * Prepares table for this RDBMS
     */
    protected function prepareTable(
        string $tableName,
        string | null $schemaName = null,
        string $alias = "",
        string $escapeChar = ""
    ): string {
        $tableName = $this->escape($tableName, $escapeChar);

        /**
         * Schema
         */
        if (!empty($schemaName)) {
            $tableName = $this->escapeSchema($schemaName, $escapeChar)
                . "." . $tableName;
        }

        /**
         * Alias
         */
        if (!empty($alias)) {
            $tableName .= " AS " . $this->escape($alias, $escapeChar);
        }

        return $tableName;
    }
}
