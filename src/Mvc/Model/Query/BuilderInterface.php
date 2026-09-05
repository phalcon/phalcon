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

namespace Phalcon\Mvc\Model\Query;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\Model\QueryInterface;

/**
 * Interface for Phalcon\Mvc\Model\Query\Builder
 *
 * @phpstan-import-type mvc_model_bind_params from MvcTypes
 * @phpstan-import-type mvc_model_bind_types from MvcTypes
 * @phpstan-import-type mvc_query_columns from MvcTypes
 * @phpstan-import-type mvc_query_order from MvcTypes
 */
interface BuilderInterface
{
    public const OPERATOR_AND = "and";
    public const OPERATOR_OR  = "or";

    /**
     * Add a model to take part of the query
     *
     * @param string      $model
     * @param string|null $alias
     *
     * @return BuilderInterface
     */
    public function addFrom(
        string $model,
        string | null $alias = null
    ): BuilderInterface;

    /**
     * Appends a condition to the current conditions using a AND operator
     *
     * @param string $conditions
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function andWhere(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface;

    /**
     * Appends a BETWEEN condition to the current conditions
     *
     * @param string $expr
     * @param mixed  $minimum
     * @param mixed  $maximum
     * @param string $operator
     *
     * @return BuilderInterface
     */
    public function betweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface;

    /**
     * Sets the columns to be queried. The columns can be either a `string` or
     * an `array` of strings. If the argument is a (single, non-embedded) string,
     * its content can specify one or more columns, separated by commas, the same
     * way that one uses the SQL select statement. You can use aliases, aggregate
     * functions, etc. If you need to reference other models you will need to
     * reference them with their namespaces.
     *
     * When using an array as a parameter, you will need to specify one field
     * per array element. If a non-numeric key is defined in the array, it will
     * be used as the alias in the query
     *
     *```php
     * <?php
     *
     * // String, comma separated values
     * $builder->columns("id, name");
     *
     * // Array, one column per element
     * $builder->columns(
     *     [
     *         "inv_id",
     *         "inv_title",
     *     ]
     * );
     *
     * // Array, named keys. The name of the key acts as an alias (`AS` clause)
     * $builder->columns(
     *     [
     *         "inv_title",
     *         "number" => "COUNT(*)",
     *     ]
     * );
     *
     * // Different models
     * $builder->columns(
     *     [
     *         "\Phalcon\Models\Invoices.*",
     *         "\Phalcon\Models\Customers.cst_name_first",
     *         "\Phalcon\Models\Customers.cst_name_last",
     *     ]
     * );
     *```
     *
     * @param mixed $columns
     *
     * @return BuilderInterface
     *
     */
    public function columns(mixed $columns): BuilderInterface;

    /**
     * Sets SELECT DISTINCT / SELECT ALL flag
     *
     *```php
     * $builder->distinct("status");
     * $builder->distinct(null);
     *```
     *
     * @param mixed $distinct
     *
     * @return BuilderInterface
     */
    public function distinct(mixed $distinct): BuilderInterface;

    /**
     * Sets a FOR UPDATE clause
     *
     *```php
     * $builder->forUpdate(true);
     *```
     *
     * @param bool $forUpdate
     *
     * @return BuilderInterface
     */
    public function forUpdate(bool $forUpdate): BuilderInterface;

    /**
     * Sets the models who makes part of the query
     *
     * @param mixed $models
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_query_columns $models
     */
    public function from(mixed $models): BuilderInterface;

    /**
     * Returns default bind params
     *
     * @return array
     *
     * @phpstan-return mvc_model_bind_params
     */
    public function getBindParams(): array;

    /**
     * Returns default bind types
     *
     * @return array
     *
     * @phpstan-return mvc_model_bind_types
     */
    public function getBindTypes(): array;

    /**
     * Return the columns to be queried
     *
     * @return array|string|null
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getColumns();

    /**
     * Returns SELECT DISTINCT / SELECT ALL flag
     *
     * @return bool
     */
    public function getDistinct(): bool;

    /**
     * Return the models who makes part of the query
     *
     * @return array|string|null
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getFrom();

    /**
     * Returns the GROUP BY clause
     *
     * @return array
     *
     * @phpstan-return array<array-key, string>
     */
    public function getGroupBy(): array;

    /**
     * Returns the HAVING condition clause
     *
     * @return string|null
     */
    public function getHaving(): string | null;

    /**
     * Return join parts of the query
     *
     * @return array
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function getJoins(): array;

    /**
     * Returns the current LIMIT clause
     *
     * @return array|string
     *
     * @phpstan-return array<array-key, mixed>|int|string|null
     */
    public function getLimit();

    /**
     * Returns the models involved in the query
     *
     * @return array|string|null
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getModels(): array | string | null;

    /**
     * Returns the current OFFSET clause
     *
     * @return int
     */
    public function getOffset(): int;

    /**
     * Return the set ORDER BY clause
     *
     * @return array|string|null
     *
     * @phpstan-return array<array-key, int|string>|string|null
     */
    public function getOrderBy();

    /**
     * Returns a PHQL statement built based on the builder parameters
     *
     * @return string
     */
    public function getPhql(): string;

    /**
     * Returns the query built
     *
     * @return QueryInterface
     */
    public function getQuery(): QueryInterface;

    /**
     * Return the conditions for the query
     *
     * @return array|string|null
     *
     * @phpstan-return array<array-key, mixed>|string|null
     */
    public function getWhere();

    /**
     * Sets a GROUP BY clause
     *
     * @param mixed $group
     *
     * @return BuilderInterface
     *
     * @phpstan-param array<array-key, string>|string|null $group
     */
    public function groupBy(mixed $group): BuilderInterface;

    /**
     * Sets a HAVING condition clause
     *
     * @param string $conditions
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function having(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface;

    /**
     * Adds an INNER join to the query
     *
     * @param string      $model
     * @param string|null $conditions
     * @param string|null $alias
     *
     * @return BuilderInterface
     */
    public function innerJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface;

    /**
     * Appends an IN condition to the current conditions
     *
     * @param string $expr
     * @param array  $values
     * @param string $operator
     *
     * @return BuilderInterface
     *
     * @phpstan-param array<array-key, mixed> $values
     */
    public function inWhere(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface;

    /**
     * Adds an :type: join (by default type - INNER) to the query
     *
     * @param string      $model
     * @param string|null $conditions
     * @param string|null $alias
     *
     * @return BuilderInterface
     */
    public function join(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface;

    /**
     * Adds a LEFT join to the query
     *
     * @param string      $model
     * @param string|null $conditions
     * @param string|null $alias
     *
     * @return BuilderInterface
     */
    public function leftJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface;

    /**
     * Sets a LIMIT clause
     *
     * @param int        $limit
     * @param mixed|null $offset
     *
     * @return BuilderInterface
     */
    public function limit(int $limit, mixed $offset = null): BuilderInterface;

    /**
     * Appends a NOT BETWEEN condition to the current conditions
     *
     * @param string $expr
     * @param mixed  $minimum
     * @param mixed  $maximum
     * @param string $operator
     *
     * @return BuilderInterface
     */
    public function notBetweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface;

    /**
     * Appends a NOT IN condition to the current conditions
     *
     * @param string $expr
     * @param array  $values
     * @param string $operator
     *
     * @return BuilderInterface
     *
     * @phpstan-param array<array-key, mixed> $values
     */
    public function notInWhere(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface;

    /**
     * Sets an OFFSET clause
     *
     * @param int $offset
     *
     * @return BuilderInterface
     */
    public function offset(int $offset): BuilderInterface;

    /**
     * Sets an ORDER BY condition clause
     *
     * @param mixed $orderBy
     *
     */
    public function orderBy(mixed $orderBy): BuilderInterface;

    /**
     * Appends a condition to the current conditions using an OR operator
     *
     * @param string $conditions
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function orWhere(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface;

    /**
     * Adds a RIGHT join to the query
     *
     * @param string      $model
     * @param string|null $conditions
     * @param string|null $alias
     *
     * @return BuilderInterface
     */
    public function rightJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface;

    /**
     * Set default bind parameters
     *
     * @param array $bindParams
     * @param bool  $merge
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     */
    public function setBindParams(
        array $bindParams,
        bool $merge = false
    ): BuilderInterface;

    /**
     * Set default bind types
     *
     * @param array $bindTypes
     * @param bool  $merge
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function setBindTypes(
        array $bindTypes,
        bool $merge = false
    ): BuilderInterface;

    /**
     * Sets conditions for the query
     *
     * @param string $conditions
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return BuilderInterface
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function where(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface;

    // TODO v7: promote the custom resultset-row-class accessors into this
    // interface. They are implemented on Phalcon\Mvc\Model\Query\Builder but
    // are kept out of the contract in 5.x to avoid breaking existing
    // BuilderInterface implementations in the wild.
    //
    // public function getResultsetRowClass(): string;
    // public function setResultsetRowClass(string $resultsetRowClass): BuilderInterface;
}
