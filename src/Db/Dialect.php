<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Db;

/**
 * This is the base class to each database dialect. This implements
 * common methods to transform intermediate code into its RDBMS related syntax
 */
abstract class Dialect implements DialectInterface
{
    protected ?string $escapeChar;

    protected $customFunctions;

    /**
     * Generate SQL to create a new savepoint
     */
    public function createSavepoint(string $name): string
    {
        return "SAVEPOINT " . name;
    }

    /**
     * Escape identifiers
     */
    final public function escape(string $str, ?string $escapeChar = null): string
    {
        //var parts, key, part, newParts;

        if (!\globals_get("db.escape_identifiers")) {
            return $str;
        }

        if (empty($escapeChar)) {
            $escapeChar = (string) $this->escapeChar;
        }

        if (strpos($str, ".")===false) {
            if (!empty($escapeChar) && ($str !== "*")) {
                return $escapeChar . str_replace($escapeChar, $escapeChar . $escapeChar, $str) . $escapeChar;
            }

            return $str;
        }

        $parts = explode(".", trim($str, $escapeChar));

        $newParts = $parts;

        foreach($parts as $key => $part){
            if (empty($escapeChar) || empty($part) || ($part === "*")) {
                continue;
            }

            $newParts[$key] = $escapeChar . str_replace($escapeChar, $escapeChar . $escapeChar, $part) . $escapeChar;
        }

        return implode(".", $newParts);
    }

    /**
     * Escape Schema
     */
    final public function escapeSchema(string $str, ?string $escapeChar = null): string
    {
        if (!\globals_get("db.escape_identifiers")) {
            return $str;
        }

        if (empty($escapeChar)) {
            $escapeChar = $this->escapeChar;
        }

        return $escapeChar . trim($str, $escapeChar) . $escapeChar;
    }

    /**
     * Returns a SQL modified with a FOR UPDATE clause
     *
     *```php
     * $sql = $dialect->forUpdate("SELECT * FROM robots");
     *
     * echo $sql; // SELECT * FROM robots FOR UPDATE
     *```
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
     */
    final public function getColumnList(array $columnList, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $columns = [];

        foreach($columnList as $column){
            $columns[] = $this->getSqlColumn($column, $escapeChar, $bindCounts);
        }

        return join(", ", $columns);
    }

    /**
     * Returns registered functions
     */
    public function getCustomFunctions(): array
    {
        return $this->customFunctions;
    }

    /**
     * Resolve Column $expressions
     */
    final public function getSqlColumn($column, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        //var columnExpression, columnAlias, columnField, columnDomain;

        if (!is_array($column)) {
            return $this->prepareQualified($column, null, $escapeChar);
        }

        if (!isset($column["type"])) {
            /**
             * The index "0" is the column field
             */
            $columnField = $column[0];

            if (is_array($columnField)) {
                $columnExpression = [
                    "type" => "scalar",
                    "value" => $columnField
                ];
            } elseif ($columnField === "*") {
                $columnExpression = [
                    "type" => "all"
                ];
            } else {
                $columnExpression = [
                    "type" => "qualified",
                    "name" => $columnField
                ];
            }

            /**
             * The index "1" is the domain column
             */
            $columnDomain = $column[1] ?? null;
            if (!empty($columnDomain)) {
                $columnExpression["domain"] = $columnDomain;
            }

            /**
             * The index "2" is the column alias
             */
            $columnAlias = $column[2] ?? null;
            if (!empty($columnAlias)) {
                $columnExpression["sqlAlias"] = $columnAlias;
            }
        } else {
            $columnExpression = $column;
        }

        /**
         * Resolve column $expressions
         */
        $column = $this->getSqlExpression(
            $columnExpression,
            $escapeChar,
            $bindCounts
        );

        /**
         * Escape alias and concatenate to value SQL
         */
        $colAlias = $columnExpression["sqlAlias"] ?? null;
        if ($colAlias === null) {
            $colAlias = $columnExpression["alias"] ?? null;
        }
        if ($colAlias !== null) {
            return $this->prepareColumnAlias($column, $columnAlias, $escapeChar);
        }

        return $this->prepareColumnAlias($column, null, $escapeChar);
    }

    /**
     * Transforms an intermediate representation for an $expression into a database system valid $expression
     */
    public function getSqlExpression(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        /*int i;
        var type, times, postTimes, rawValue, value;
        array placeholders;*/

        $type = $expression["type"] ?? null;
        if (!is_string($type)) {
            throw new Exception("Invalid SQL $expression");
        }

        switch ($type) {

            /**
             * Resolve scalar column $expressions
             */
            case "scalar":
                return $this->getSqlExpressionScalar(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve object $expressions
             */
            case "object":
                return $this->getSqlExpressionObject(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve qualified $expressions
             */
            case "qualified":
                return $this->getSqlExpressionQualified($expression, $escapeChar);

            /**
             * Resolve literal OR placeholder $expressions
             */
            case "literal":
                return $expression["value"];

            case "placeholder":
                $times = $expression["times"] ?? null;
                if ($times !== null) {
                    $placeholders = [];
                    $rawValue = $expression["rawValue"];
                    $value = $expression["value"];
                    $postTimes = $bindCounts[$rawValue] ?? null;
                    if ($postTimes !== null) {
                        $times = $postTimes;
                    }

                    foreach(range(1, $times) as $i) {
                        $placeholders[] = $value . ($i - 1);
                    }

                    return join(", ", $placeholders);
                }

                return $expression["value"];

            /**
             * Resolve binary operations $expressions
             */
            case "binary-op":
                return $this->getSqlExpressionBinaryOperations(
                    $expression,
                    $escapeChar,
                    $bindCounts
                );

            /**
             * Resolve unary operations $expressions
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
                return "(" . $this->getSqlExpression($expression["left"], $escapeChar, $bindCounts) . ")";

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
        throw new Exception("Invalid SQL $expression type '" . $type . "'");
    }

    /**
     * Transform an intermediate representation of a schema/table into a
     * database system valid $expression
     */
    final public function getSqlTable($table, ?string $escapeChar = null): string
    {
        //var tableName, schemaName, aliasName;

        if (is_array($table)) {

            /**
             * The index "0" is the table name
             */
            $tableName = $table[0];
            $schemaName = $table[1] ?? null;
            $aliasName = $table[2] ?? null;

            return $this->prepareTable(
                $tableName,
                $schemaName,
                $aliasName,
                $escapeChar
            );
        }

        return $this->escape($table, $escapeChar);
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
     */
    public function limit(string $sqlQuery, $number): string
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
     */
    public function registerCustomFunction(string $name, callable $customFunction) : Dialect
    {
        $$this->customFunctions[$name] = $customFunction;

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
     * Generate SQL to rollback a savepoint
     */
    public function rollbackSavepoint(string $name): string
    {
        return "ROLLBACK TO SAVEPOINT " . $name;
    }

    /**
     * Builds a SELECT statement
     */
    public function select(array $definition): string
    {
        /* var tables, columns, sql, distinct, joins, where, $escapeChar, groupBy,
            having, orderBy, limit, forUpdate, ?int $bindCounts; */
        $tables = $definition["tables"] ?? null;
        if ($tables===null)  {
            throw new Exception(
                "The index 'tables' is required in the definition array"
            );
        }
        $columns = $definition["columns"] ?? null;
        if ($columns===null)  {
            throw new Exception(
                "The index 'columns' is required in the definition array"
            );
        }
        $distinct = $definition["distinct"] ?? null;
        if ($distinct !== null) {
            if ($distinct){
                $sql = "SELECT DISTINCT";
            } else {
                $sql = "SELECT ALL";
            }
        } else {
            $sql = "SELECT";
        }
        $bindCounts = $definition["bindCounts"] ?? null;

        $escapeChar = $this->escapeChar;

        /**
         * Resolve COLUMNS
         */
        $sql .= " " . $this->getColumnList($columns, $escapeChar, $bindCounts);

        /**
         * Resolve FROM
         */
        $sql .= " " . $this->getSqlExpressionFrom($tables, $escapeChar);

        /**
         * Resolve JOINs
         */
        $joins = $definition["joins"] ?? null;
        if (!empty($joins)) {
            $sql .= " " . $this->getSqlExpressionJoins($joins, $escapeChar, $bindCounts);
        }

        /**
         * Resolve WHERE
         */
        $where = $definition["where"] ?? null;
        if (!empty($where)) {
            $sql .= " " . $this->getSqlExpressionWhere($where, $escapeChar, $bindCounts);
        }

        /**
         * Resolve GROUP BY
         */
        $groupBy = $definition["group"] ?? null;
        if (!empty($groupBy)) {
            $sql .= " " . $this->getSqlExpressionGroupBy($groupBy, $escapeChar);
        }

        /**
         * Resolve HAVING
         */
        $having = $definition["having"] ?? null;
        if (!empty($having)) {
            $sql .= " " . $this->getSqlExpressionHaving($having, $escapeChar, $bindCounts);
        }

        /**
         * Resolve ORDER BY
         */
        $orderBy = $definition["order"] ?? null;
        if (!empty($orderBy)) {
            $sql .= " " . $this->getSqlExpressionOrderBy($orderBy, $escapeChar, $bindCounts);
        }

        /**
         * Resolve LIMIT
         */
        $limit = $definition["limit"] ?? null;
        if (!empty($limit)) {
            $sql = $this->getSqlExpressionLimit(
                [
                    "sql" => $sql,
                    "value" => $limit
                ],
                $escapeChar,
                $bindCounts
            );
        }

        /**
         * Resolve FOR UPDATE
         */
        $forUpdate = $definition["forUpdate"] ?? null;
        if (!empty($forUpdate)) {
            $sql .= " FOR UPDATE";
        }

        return $sql;
    }

    /**
     * Checks whether the platform supports savepoints
     */
    public function supportsSavepoints(): bool
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
     * Returns the size of the column enclosed in parentheses
     */
    protected function getColumnSize( ColumnInterface $column): string
    {
        return "(" . $column->getSize() . ")";
    }

    /**
     * Returns the column size and scale enclosed in parentheses
     */
    protected function getColumnSizeAndScale( ColumnInterface $column): string
    {
        return "(" . $column->getSize() . "," . $column->getScale() . ")";
    }

    /**
     * Checks the column type and if not string it returns the type reference
     */
    protected function checkColumnType( ColumnInterface $column): string
    {
        $col = $column->getType();
        if (is_string($col)) {
            return $column->getTypeReference();
        }

        return (string)$col;
    }

    /**
     * Checks the column type and returns the updated SQL statement
     */
    protected function checkColumnTypeSql( ColumnInterface $column): string
    {
        $col = $column->getType();
        if (!is_string($col)) {
            return "";
        }

        return $col;
    }

    /**
     * Resolve *
     */
    final protected function getSqlExpressionAll(array $expression, ?string $escapeChar = null): string
    {
        $domain = $expression["domain"] ?? null;

        return $this->prepareQualified("*", $domain, $escapeChar);
    }

    /**
     * Resolve binary operations $expressions
     */
    final protected function getSqlExpressionBinaryOperations(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $left  = $this->getSqlExpression(
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
     * Resolve CASE $expressions
     */
    final protected function getSqlExpressionCase(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $sql = "CASE " . $this->getSqlExpression(expression["expr"], $escapeChar,  $bindCounts);

        foreach($expression["when-clauses"] as $whenClause)  {
            if ($whenClause["type"] === "when") {
                $sql .= " WHEN " .
                        $this->getSqlExpression($whenClause["expr"], $escapeChar,  $bindCounts) .
                        " THEN " .
                        $this->getSqlExpression($whenClause["then"], $escapeChar,  $bindCounts);
            } else {
                $sql .= " ELSE " . $this->getSqlExpression($whenClause["expr"], $escapeChar,  $bindCounts);
            }
        }

        return $sql . " END";
    }

    /**
     * Resolve CAST of values
     */
    final protected function getSqlExpressionCastValue(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $left  = $this->getSqlExpression(
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
     */
    final protected function getSqlExpressionConvertValue(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $left  = $this->getSqlExpression(
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
     */
    final protected function getSqlExpressionFrom($expression, ?string $escapeChar = null): string
    {
        if (is_array($expression)) {
            $tables = [];

            foreach(expression as $table) {
                $tables[] = $this->getSqlTable($table, $escapeChar);
            }

            $tables = join(", ", $tables);
        } else {
            $tables = $expression;
        }

        return "FROM " . $tables;
    }

    /**
     * Resolve function calls
     */
    final protected function getSqlExpressionFunctionCall(array $expression, ?string $escapeChar = null, ?int $bindCounts): string
    {
        $name = $expression["name"];
        $customFunction = $this->customFunctions[$name] ?? null;
        if ($customFunction !== null){
            return  $customFunction($this, $expression, $escapeChar);
        }
        $arguments = $expression["arguments"] ?? null;
        if (is_array($arguments)) {

            $arguments = $this->getSqlExpression(
                [
                    "type" =>        "list",
                    "parentheses" =>   false,
                    "value" =>        $arguments
                ],
                $escapeChar,
                $bindCounts
            );
            
            $isDistinct = $expression["distinct"] ?? null;
            if ($isDistinct) {
                return $name . "(DISTINCT " . $arguments . ")";
            }

            return $name . "(" . $arguments . ")";
        }

        return $name . "()";
    }

    /**
     * Resolve a GROUP BY clause
     */
    final protected function getSqlExpressionGroupBy( $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        
        if (is_array($expression)) {
            $fields = [];

            foreach($expression as $field) {
                if (!is_array($field)) {
                    throw new Exception("Invalid SQL-GROUP-BY $expression");
                }

                $fields[] = $this->getSqlExpression(
                    $field,
                    $escapeChar,
                    $bindCounts
                );
            }

            $fields = join(", ", fields);
        } else {
            $fields = $expression;
        }

        return "GROUP BY " . $fields;
    }

    /**
     * Resolve a HAVING clause
     */
    final protected function getSqlExpressionHaving(array  $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        return "HAVING " . $this->getSqlExpression($expression, $escapeChar, $bindCounts);
    }

    /**
     * Resolve a JOINs clause
     */
    final protected function getSqlExpressionJoins( $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $sql = "";

        foreach($expression as $join) {
            /**
             * Check if the join has conditions
             */
            $joinConditionsArray = $join["conditions"] ?? null;
            if (!empty($joinConditionsArray)) {
                if (!isset($joinConditionsArray[0])) {
                    $joinCondition = $this->getSqlExpression(
                        $joinConditionsArray,
                        $escapeChar,
                        $bindCounts
                    );
                } else {
                    $joinCondition = [];

                    foreach($joinConditionsArray as $condition) {
                        $joinCondition[] = $this->getSqlExpression(
                            $condition,
                            $escapeChar,
                            $bindCounts
                        );
                    }

                    $joinCondition = join(" AND ", $joinCondition);
                }
            } else {
                $joinCondition = 1;
            }
            $joinType = $join["type"] ?? null;
            if (!empty($joinType)) {
                $joinType .= " ";
            }

            $joinTable = $this->getSqlTable($join["source"], $escapeChar);

            $sql .= " " . $joinType . "JOIN " . $joinTable . " ON " . $joinCondition;
        }

        return $sql;
    }

    /**
     * Resolve a LIMIT clause
     */
    final protected function getSqlExpressionLimit($expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $offset = null;
        $value = $expression["value"];
        $sql = $expression["sql"] ?? "";


        if (is_array($value)) {
            $nval = $value["number"];
            if (is_array($nval)) {
                $limit = $this->getSqlExpression(
                    $nval,
                    $escapeChar,
                    $bindCounts
                );
            } else {
                $limit = $nval;
            }

            /**
             * Check for an OFFSET condition
             */
            $offset = $value["offset"] ?? null;
            if (is_array($offset)) {
                $offset = $this->getSqlExpression(
                    $offset,
                    $escapeChar,
                    $bindCounts
                );
            }

        } else {
            $limit = $value;
        }

        return $this->limit($sql, [$limit, $offset]);
    }

    /**
     * Resolve Lists
     */
    final protected function getSqlExpressionList(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $items = [];
        $separator = $expression["separator"] ?? ", ";
        $values = $expression[0] ?? null;
        if ($values === null) {
            $values = $expression["value"] ?? null;
        }
        if (is_array($values)) {

            foreach($values as $item) {
                $items[] = $this->getSqlExpression($item, $escapeChar,  $bindCounts);
            }
            $hasParen = $expression["parentheses"] ?? null;
            if (empty($hasParen)) {
                return join($separator, $items);
            }

            return "(" . join($separator, $items) . ")";
        }

        throw new Exception("Invalid SQL-list $expression");
    }

    /**
     * Resolve object $expressions
     */
    final protected function getSqlExpressionObject(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
       

        $objectExpression = [
            "type" => "all"
        ];
        $domain = $expression["column"] ?? null;
        if ($domain === null) {
            $domain =  $expression["domain"] ?? null;
        }
        if (!empty($domain)) {
            $objectExpression["domain"] = $domain;
        }

        return $this->getSqlExpression($objectExpression, $escapeChar, $bindCounts);
    }

    /**
     * Resolve an ORDER BY clause
     */
    final protected function getSqlExpressionOrderBy( $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        
        $fieldSql = null;

        if (is_array($expression)) {
            $fields = [];

            foreach($expression as $field ) 
            {
                if (!is_array($field)) {
                    throw new Exception("Invalid SQL-ORDER-BY $expression");
                }

                $fieldSql = $this->getSqlExpression(
                    $field[0],
                    $escapeChar,
                    $bindCounts
                );

                /**
                 * In the numeric 1 position could be a ASC/DESC clause
                 */
                $type = $field[1] ?? null;
                if (!empty($type)) {
                    $fieldSql .= " " . $type;
                }

                $fields[] = $fieldSql;
            }

            $fields = join(", ", $fields);
        } else {
            $fields = $expression;
        }

        return "ORDER BY " . $fields;
    }

    /**
     * Resolve qualified $expressions
     */
    final protected function getSqlExpressionQualified(array $expression, ?string $escapeChar = null): string
    {
        $column = $expression["name"];
        /**
         * A domain could be a table/schema
         */
        $domain = $expression["domain"] ?? null;

        return $this->prepareQualified($column, $domain, $escapeChar);
    }

    /**
     * Resolve Column $expressions
     */
    final protected function getSqlExpressionScalar(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        $col = $expression["column"] ?? null;
        if ($col !== null) {
            return $this->getSqlColumn($col);
        }
        $value = $expression["value"] ?? null;
        if ($value === null) {
            throw new Exception("Invalid SQL $expression");
        }

        if (is_array($value)) {
            return $this->getSqlExpression($value, $escapeChar,  $bindCounts);
        }

        return $value;
    }

    /**
     * Resolve unary operations $expressions
     */
    final protected function getSqlExpressionUnaryOperations(array $expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        
        /**
         * Some unary operators use the left operand...
         */
        $left = $expression["left"] ?? null;
        if ($left !== null) {
            return $this->getSqlExpression($left, $escapeChar,  $bindCounts) . " " . $expression["op"];
        }

        /**
         * ...Others use the right operand
         */
        $right = $expression["right"] ?? null;
        if ($right !== null) {
            return $expression["op"] . " " . $this->getSqlExpression($right, $escapeChar, $bindCounts);
        }

        throw new Exception("Invalid SQL-unary $expression");
    }

    /**
     * Resolve a WHERE clause
     */
    final protected function getSqlExpressionWhere($expression, ?string $escapeChar = null, ?int $bindCounts = null): string
    {
        
        if (is_array($expression)){
            $whereSql = $this->getSqlExpression($expression, $escapeChar, $bindCounts);
        } else {
            $whereSql = $expression;
        }

        return "WHERE " . $whereSql;
    }

    /**
     * Prepares column for this RDBMS
     */
    protected function prepareColumnAlias(string $qualified, ?string $alias = null, ?string $escapeChar = null): string
    {
        if (!empty($alias)) {
            return $qualified . " AS " . $this->escape($alias, $escapeChar);
        }

        return $qualified;
    }

    /**
     * Prepares table for this RDBMS
     */
    protected function prepareTable(string $table, ?string $schema = null, ?string $alias = null, ?string $escapeChar = null): string
    {
        $table = $this->escape($table, $escapeChar);

        /**
         * Schema
         */
        if (!empty($schema)){
            $table = $this->escapeSchema($schema, $escapeChar) . "." . $table;
        }

        /**
         * Alias
         */
        if (!empty($alias)) {
            $table = $table . " AS " . $this->escape($alias, $escapeChar);
        }

        return $table;
    }

    /**
     * Prepares qualified for this RDBMS
     */
    protected function prepareQualified(string $column, ?string $domain = null, ?string $escapeChar = null): string
    {
        if (!empty($domain)) {
            return $this->escape($domain . "." . $column, $escapeChar);
        }

        return $this->escape($column, $escapeChar);
    }
}
