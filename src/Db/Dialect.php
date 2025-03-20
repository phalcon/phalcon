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

use Phalcon\Support\Settings;

use function explode;
use function implode;
use function is_array;
use function is_string;
use function range;
use function str_replace;
use function strlen;
use function trim;

/**
 * This is the base class to each database dialect. This implements
 * common methods to transform intermediate code into its RDBMS related syntax
 */
abstract class Dialect implements DialectInterface
{
    /**
     * @var array
     */
    protected array $customFunctions = [];
    /**
     * @var string
     */
    protected string $escapeChar;

    /**
     * Generate SQL to create a new savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function createSavepoint(string $name): string
    {
        return "SAVEPOINT " . $name;
    }

    /**
     * Escape identifiers
     *
     * @param string $input
     * @param string $escapeChar
     *
     * @return string
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
     *
     * @param string $input
     * @param string $escapeChar
     *
     * @return string
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
     * Returns a SQL modified with a FOR UPDATE clause
     *
     *```php
     * $sql = $dialect->forUpdate("SELECT * FROM robots");
     *
     * echo $sql; // SELECT * FROM robots FOR UPDATE
     *```
     *
     * @param string $sqlQuery
     *
     * @return string
     */
    public function forUpdate(string $sqlQuery): string
    {
        return $sqlQuery . " FOR UPDATE";
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
     * @param array  $columnList
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
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
     * @return array
     */
    public function getCustomFunctions(): array
    {
        return $this->customFunctions;
    }

    /**
     * Resolve Column expressions
     *
     * @param array|string $column
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
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
            $columnAlias = $columnExpression["sqlAlias"] ?? null;
            $columnAlias = (null === $columnAlias) ? $columnExpression["alias"] : $columnAlias;

            return $this->prepareColumnAlias($column, $columnAlias, $escapeChar);
        }

        return $this->prepareColumnAlias($column, "", $escapeChar);
    }

    /**
     * Transforms an intermediate representation for an expression into a
     * database system valid expression
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    public function getSqlExpression(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (!isset($expression["type"])) {
            throw new Exception("Invalid SQL expression");
        }

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
                return $expression["value"];

            case "placeholder":
                if (isset($expression["times"])) {
                    $placeholders = [];
                    $rawValue     = $expression["rawValue"];
                    $value        = $expression["value"];
                    $times        = $expression["times"];

                    if (isset($bindCounts[$rawValue])) {
                        $times = $bindCounts[$rawValue];
                    }

                    foreach (range(1, $times) as $counter) {
                        $placeholders[] = $value . ($counter - 1);
                    }

                    return implode(", ", $placeholders);
                }

                return $expression["value"];

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
                return "("
                    . $this->getSqlExpression(
                        $expression["left"],
                        $escapeChar,
                        $bindCounts
                    )
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
             */
            case "select":
                return "(" . $this->select($expression["value"]) . ")";

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
        throw new Exception("Invalid SQL expression type '" . $type . "'");
    }

    /**
     * Transform an intermediate representation of a schema/table into a
     * database system valid expression
     *
     * @param array|string $tableName
     * @param string       $escapeChar
     *
     * @return string
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
                $tableName[0] ?? null,
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
     * // SELECT * FROM robots LIMIT 10
     * echo $dialect->limit(
     *     "SELECT * FROM robots",
     *     10
     * );
     *
     * // SELECT * FROM robots LIMIT 10 OFFSET 50
     * echo $dialect->limit(
     *     "SELECT * FROM robots",
     *     [10, 50]
     * );
     * ```
     *
     * @param string    $sqlQuery
     * @param array|int $number
     *
     * @return string
     */
    public function limit(string $sqlQuery, array | int $number): string
    {
        if (is_array($number)) {
            $sqlQuery .= " LIMIT " . $number[0];

            if (isset($number[1]) && strlen($number[1])) {
                $sqlQuery .= " OFFSET " . $number[1];
            }

            return $sqlQuery;
        }

        return $sqlQuery . " LIMIT " . $number;
    }

    /**
     * Registers custom SQL functions
     *
     * @param string   $name
     * @param callable $customFunction
     *
     * @return $this
     */
    public function registerCustomFunction(
        string $name,
        callable $customFunction
    ): Dialect {
        $this->customFunctions[$name] = $customFunction;

        return $this;
    }

    /**
     * Generate SQL to release a savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function releaseSavepoint(string $name): string
    {
        return "RELEASE SAVEPOINT " . $name;
    }

    /**
     * Generate SQL to rollback a savepoint
     *
     * @param string $name
     *
     * @return string
     */
    public function rollbackSavepoint(string $name): string
    {
        return "ROLLBACK TO SAVEPOINT " . $name;
    }

    /**
     * Builds a SELECT statement
     *
     * @param array $definition
     *
     * @return string
     * @throws Exception
     */
    public function select(array $definition): string
    {
        if (!isset($definition["tables"])) {
            throw new Exception(
                "The index 'tables' is required in the definition array"
            );
        }

        if (!isset($definition["columns"])) {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }

        $bindCounts = $definition["bindCounts"] ?? [];
        $columns    = $definition["columns"];
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
            $sql .= " "
                . $this->getSqlExpressionJoins(
                    $definition["joins"],
                    $escapeChar,
                    $bindCounts
                );
        }

        /**
         * Resolve WHERE
         */
        if (
            isset($definition["where"]) &&
            !empty($definition["where"])
        ) {
            $sql .= " "
                . $this->getSqlExpressionWhere(
                    $definition["where"],
                    $escapeChar,
                    $bindCounts
                );
        }

        /**
         * Resolve GROUP BY
         */
        if (
            isset($definition["group"]) &&
            !empty($definition["group"])
        ) {
            $sql .= " "
                . $this->getSqlExpressionGroupBy(
                    $definition["group"],
                    $escapeChar
                );
        }

        /**
         * Resolve HAVING
         */
        if (
            isset($definition["having"]) &&
            !empty($definition["having"])
        ) {
            $sql .= " "
                . $this->getSqlExpressionHaving(
                    $definition["having"],
                    $escapeChar,
                    $bindCounts
                );
        }

        /**
         * Resolve ORDER BY
         */
        if (
            isset($definition["order"]) &&
            !empty($definition["order"])
        ) {
            $sql .= " "
                . $this->getSqlExpressionOrderBy(
                    $definition["order"],
                    $escapeChar,
                    $bindCounts
                );
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
     * Checks whether the platform supports releasing savepoints.
     *
     * @return bool
     */
    public function supportsReleaseSavepoints(): bool
    {
        return $this->supportsSavePoints();
    }

    /**
     * Checks whether the platform supports savepoints
     *
     * @return bool
     */
    public function supportsSavepoints(): bool
    {
        return true;
    }

    /**
     * Checks the column type and if not string it returns the type reference
     *
     * @param ColumnInterface $column
     *
     * @return int
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
     * @param ColumnInterface $column
     *
     * @return string
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
     * Returns the size of the column enclosed in parentheses
     *
     * @param ColumnInterface $column
     *
     * @return string
     */
    protected function getColumnSize(ColumnInterface $column): string
    {
        return "(" . $column->getSize() . ")";
    }

    /**
     * Returns the column size and scale enclosed in parentheses
     *
     * @param ColumnInterface $column
     *
     * @return string
     */
    protected function getColumnSizeAndScale(ColumnInterface $column): string
    {
        return "(" . $column->getSize() . "," . $column->getScale() . ")";
    }

    /**
     * Resolve *
     *
     * @param array  $expression
     * @param string $escapeChar
     *
     * @return string
     */
    final protected function getSqlExpressionAll(
        array $expression,
        string $escapeChar = ""
    ): string {
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
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionBinaryOperations(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $left = $this->getSqlExpression(
            $expression["left"],
            $escapeChar,
            $bindCounts
        );

        $right = $this->getSqlExpression(
            $expression["right"],
            $escapeChar,
            $bindCounts
        );

        return $left . " " . $expression["op"] . " " . $right;
    }

    /**
     * Resolve CASE expressions
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionCase(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $sql = "CASE " . $this->getSqlExpression($expression["expr"], $escapeChar, $bindCounts);

        foreach ($expression["when-clauses"] as $whenClause) {
            if ("when" === $whenClause["type"]) {
                $sql .= " WHEN "
                    . $this->getSqlExpression($whenClause["expr"], $escapeChar, $bindCounts)
                    . " THEN "
                    . $this->getSqlExpression($whenClause["then"], $escapeChar, $bindCounts);
            } else {
                $sql .= " ELSE "
                    . $this->getSqlExpression($whenClause["expr"], $escapeChar, $bindCounts);
            }
        }

        return $sql . " END";
    }

    /**
     * Resolve CAST of values
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionCastValue(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $left = $this->getSqlExpression(
            $expression["left"],
            $escapeChar,
            $bindCounts
        );

        $right = $this->getSqlExpression(
            $expression["right"],
            $escapeChar,
            $bindCounts
        );

        return "CAST(" . $left . " AS " . $right . ")";
    }

    /**
     * Resolve CONVERT of values encodings
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionConvertValue(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $left = $this->getSqlExpression(
            $expression["left"],
            $escapeChar,
            $bindCounts
        );

        $right = $this->getSqlExpression(
            $expression["right"],
            $escapeChar,
            $bindCounts
        );

        return "CONVERT(" . $left . " USING " . $right . ")";
    }

    /**
     * Resolve a FROM clause
     *
     * @param array|string $expression
     * @param string       $escapeChar
     *
     * @return string
     */
    final protected function getSqlExpressionFrom(
        array | string $expression,
        string $escapeChar = ""
    ): string {
        $tableNames = $expression;
        if (is_array($expression)) {
            $tableNames = [];

            foreach ($expression as $tableName) {
                $tableNames[] = $this->getSqlTable($tableName, $escapeChar);
            }

            $tableNames = implode(", ", $tableNames);
        }

        return "FROM " . $tableNames;
    }

    /**
     * Resolve function calls
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionFunctionCall(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $name = $expression["name"];

        if (isset($this->customFunctions[$name])) {
            $customFunction = $this->customFunctions[$name];

            return $customFunction($this, $expression, $escapeChar);
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
     * @param array|string $expression
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionGroupBy(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $fields = $expression;

        if (is_array($expression)) {
            $fields = [];
            foreach ($expression as $field) {
                if (!is_array($field)) {
                    throw new Exception("Invalid SQL-GROUP-BY expression");
                }

                $fields[] = $this->getSqlExpression(
                    $field,
                    $escapeChar,
                    $bindCounts
                );
            }

            $fields = implode(", ", $fields);
        }

        return "GROUP BY " . $fields;
    }

    /**
     * Resolve a HAVING clause
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
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
     * @param array|string $expression
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionJoins(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $sql = "";

        foreach ($expression as $join) {
            /**
             * Check if the join has conditions
             */
            $joinCondition = 1;
            $joinType      = "";
            if (
                isset($join["conditions"]) &&
                !empty($join["conditions"])
            ) {
                $joinConditionsArray = $join["conditions"];
                if (!isset($joinConditionsArray[0])) {
                    $joinCondition = $this->getSqlExpression(
                        $joinConditionsArray,
                        $escapeChar,
                        $bindCounts
                    );
                } else {
                    $joinCondition = [];
                    foreach ($joinConditionsArray as $condition) {
                        $joinCondition[] = $this->getSqlExpression(
                            $condition,
                            $escapeChar,
                            $bindCounts
                        );
                    }

                    $joinCondition = implode(" AND ", $joinCondition);
                }
            }

            if (isset($join["type"]) && $join["type"]) {
                $joinType .= " ";
            }

            $joinTable = $this->getSqlTable($join["source"], $escapeChar);

            $sql .= " " . $joinType
                . "JOIN " . $joinTable
                . " ON " . $joinCondition;
        }

        return $sql;
    }

    /**
     * Resolve a LIMIT clause
     *
     * @param array|string $expression
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionLimit(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $sql    = "";
        $offset = null;

        $value = $expression["value"];

        if (isset($expression["sql"])) {
            $sql = $expression["sql"];
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
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionList(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $items     = [];
        $separator = ", ";

        if (isset($expression["separator"])) {
            $separator = $expression["separator"];
        }

        if (
            isset($expression[0]) ||
            (isset($expression["value"]) && is_array($expression["value"]))
        ) {
            $values = $expression[0] ?? null;
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

        throw new Exception("Invalid SQL-list expression");
    }

    /**
     * Resolve object expressions
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
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
     * @param array|string $expression
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionOrderBy(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $fields = $expression;

        if (is_array($expression)) {
            $fields = [];
            foreach ($expression as $field) {
                if (!is_array($field)) {
                    throw new Exception("Invalid SQL-ORDER-BY expression");
                }

                $fieldSql = $this->getSqlExpression(
                    $field[0],
                    $escapeChar,
                    $bindCounts
                );

                /**
                 * In the numeric 1 position could be ASC/DESC clause
                 */
                if (isset($field[1]) && "" !== $field[1]) {
                    $fieldSql .= " " . $field[1];
                }

                $fields[] = $fieldSql;
            }

            $fields = implode(", ", $fields);
        }

        return "ORDER BY " . $fields;
    }

    /**
     * Resolve qualified expressions
     *
     * @param array  $expression
     * @param string $escapeChar
     *
     * @return string
     */
    final protected function getSqlExpressionQualified(
        array $expression,
        string $escapeChar = ""
    ): string {
        $column = $expression["name"];

        /**
         * A domain could be a table/schema
         */
        $domain = $expression["domain"] ?? "";

        return $this->prepareQualified($column, $domain, $escapeChar);
    }

    /**
     * Resolve Column expressions
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionScalar(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        if (isset($expression["column"])) {
            return $this->getSqlColumn($expression["column"]);
        }

        if (!isset($expression["value"])) {
            throw new Exception("Invalid SQL expression");
        }

        $value = $expression["value"];
        if (is_array($value)) {
            return $this->getSqlExpression($value, $escapeChar, $bindCounts);
        }

        return $value;
    }

    /**
     * Resolve unary operations expressions
     *
     * @param array  $expression
     * @param string $escapeChar
     * @param array  $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionUnaryOperations(
        array $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        /**
         * Some unary operators use the left operand...
         */
        if (isset($expression["left"])) {
            return $this->getSqlExpression(
                $expression["left"],
                $escapeChar,
                $bindCounts
            ) . " " . $expression["op"];
        }

        /**
         * ...Others use the right operand
         */
        if (isset($expression["right"])) {
            return $expression["op"] . " "
                . $this->getSqlExpression(
                    $expression["right"],
                    $escapeChar,
                    $bindCounts
                );
        }

        throw new Exception("Invalid SQL-unary expression");
    }

    /**
     * Resolve a WHERE clause
     *
     * @param array|string $expression
     * @param string       $escapeChar
     * @param array        $bindCounts
     *
     * @return string
     * @throws Exception
     */
    final protected function getSqlExpressionWhere(
        array | string $expression,
        string $escapeChar = "",
        array $bindCounts = []
    ): string {
        $whereSql = $expression;
        if (is_array($expression)) {
            $whereSql = $this->getSqlExpression(
                $expression,
                $escapeChar,
                $bindCounts
            );
        }

        return "WHERE " . $whereSql;
    }

    /**
     * Prepares column for this RDBMS
     *
     * @param string $qualified
     * @param string $alias
     * @param string $escapeChar
     *
     * @return string
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
     *
     * @param string $column
     * @param string $domain
     * @param string $escapeChar
     *
     * @return string
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
     *
     * @param string      $tableName
     * @param string|null $schemaName
     * @param string      $alias
     * @param string      $escapeChar
     *
     * @return string
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
