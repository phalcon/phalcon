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

namespace Phalcon\Mvc\Model;

use Phalcon\Db\Column;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;

use function abs;
use function array_merge;
use function implode;
use function is_array;
use function is_object;
use function is_string;

/**
 * This class is used to build the array parameter required by
 * Phalcon\Mvc\Model::find() and Phalcon\Mvc\Model::findFirst() using an
 * object-oriented interface.
 *
 * ```php
 * <?php
 *
 * $invoices = Invoices::query()
 *     ->where("inv_cst_id = :customerId:")
 *     ->andWhere("inv_created_date < '2000-01-01'")
 *     ->bind(["customerId" => 1])
 *     ->limit(5, 10)
 *     ->orderBy("inv_title")
 *     ->execute();
 * ```
 */
class Criteria implements CriteriaInterface, InjectionAwareInterface
{
    /**
     * @var array
     */
    protected array $bindParams;

    /**
     * @var array
     */
    protected array $bindTypes;

    /**
     * @var int
     */
    protected int $hiddenParamNumber = 0;

    /**
     * @var string|null
     */
    protected string | null $model = null;

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * Appends a condition to the current conditions using an AND operator
     *
     * @param string     $conditions
     * @param array|null $bindParams
     * @param array|null $bindTypes
     *
     * @return CriteriaInterface
     */
    public function andWhere(
        string $conditions,
        array | null $bindParams = null,
        array | null $bindTypes = null
    ): CriteriaInterface {
        if (isset($this->params["conditions"])) {
            $conditions = "(" . $this->params["conditions"] . ") AND (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a BETWEEN condition to the current conditions
     *
     *```php
     * $criteria->betweenWhere("price", 100.25, 200.50);
     *```
     */
    public function betweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum
    ): CriteriaInterface {
        $hiddenParam     = $this->hiddenParamNumber;
        $nextHiddenParam = $hiddenParam + 1;

        /**
         * Minimum key with auto bind-params
         */
        $minimumKey = "ACP" . $hiddenParam;

        /**
         * Maximum key with auto bind-params
         */
        $maximumKey = "ACP" . $nextHiddenParam;

        /**
         * Create a standard BETWEEN condition with bind params
         * Append the BETWEEN to the current conditions using and "and"
         */
        $this->andWhere(
            $expr . " BETWEEN :" . $minimumKey . ": AND :" . $maximumKey . ":",
            [
                $minimumKey => $minimum,
                $maximumKey => $maximum,
            ]
        );

        $nextHiddenParam++;
        $this->hiddenParamNumber = $nextHiddenParam;

        return $this;
    }

    /**
     * Sets the bound parameters in the criteria
     * This method replaces all previously set bound parameters
     *
     * @param array $bindParams
     * @param bool  $merge
     *
     * @return CriteriaInterface
     */
    public function bind(
        array $bindParams,
        bool $merge = false
    ): CriteriaInterface {
        if (!isset($this->params["bind"])) {
            $this->params["bind"] = [];
        }

        if (is_array($this->params["bind"]) && $merge) {
            $this->params["bind"] = $this->params["bind"] + $bindParams;
        } else {
            $this->params["bind"] = $bindParams;
        }

        return $this;
    }

    /**
     * Sets the bind types in the criteria
     * This method replaces all previously set bound parameters
     *
     * @param array $bindTypes
     *
     * @return CriteriaInterface
     */
    public function bindTypes(array $bindTypes): CriteriaInterface
    {
        $this->params["bindTypes"] = $bindTypes;

        return $this;
    }

    /**
     * Sets the cache options in the criteria
     * This method replaces all previously set cache options
     *
     * @param array $cache
     *
     * @return CriteriaInterface
     */
    public function cache(array $cache): CriteriaInterface
    {
        $this->params["cache"] = $cache;

        return $this;
    }

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
     * $criteria->columns("id, category");
     *
     * // Array, one column per element
     * $criteria->columns(
     *     [
     *         "inv_id",
     *         "inv_total",
     *     ]
     * );
     *
     * // Array with named key. The name of the key acts as an
     * // alias (`AS` clause)
     * $criteria->columns(
     *     [
     *         "inv_cst_id",
     *         "total_invoices" => "COUNT(*)",
     *     ]
     * );
     *
     * // Different models
     * $criteria->columns(
     *     [
     *         "\Phalcon\Models\Invoices.*",
     *         "\Phalcon\Models\Customers.cst_name_first",
     *         "\Phalcon\Models\Customers.cst_name_last",
     *     ]
     * );
     *```
     *
     * @param string|array $columns
     */
    public function columns(array | string $columns): CriteriaInterface
    {
        $this->params["columns"] = $columns;

        return $this;
    }

    /**
     * Adds the conditions parameter to the criteria
     */
    public function conditions(string $conditions): CriteriaInterface
    {
        $this->params["conditions"] = $conditions;

        return $this;
    }

    /**
     * Creates a query builder from criteria.
     *
     * <?php
     *
     * $invoices = Invoices::query()
     *     ->where("inv_cst_id = :customerId:")
     *     ->bind(["customerId" => 1])
     *     ->createBuilder();
     * ```
     */
    public function createBuilder(): BuilderInterface
    {
        $container = $this->getDI();

        if (!is_object($container)) {
            $container = Di::getDefault();

            $this->setDI($container);
        }

        $manager = $container->getShared("modelsManager");

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($this->params);

        $builder->from($this->model);

        return $builder;
    }

    /**
     * Sets SELECT DISTINCT / SELECT ALL flag
     *
     * @param mixed $distinct
     *
     * @return CriteriaInterface
     */
    public function distinct(mixed $distinct): CriteriaInterface
    {
        $this->params["distinct"] = $distinct;

        return $this;
    }

    /**
     * Executes a find using the parameters built with the criteria
     *
     * @return ResultsetInterface
     * @throws Exception
     */
    public function execute(): ResultsetInterface
    {
        $model = $this->getModelName();

        if (!is_string($model)) {
            throw new Exception("Model name must be string");
        }

        return $model::find($this->getParams());
    }

    /**
     * Adds the "for_update" parameter to the criteria
     *
     * @param bool $forUpdate
     *
     * @return CriteriaInterface
     */
    public function forUpdate(bool $forUpdate = true): CriteriaInterface
    {
        $this->params["for_update"] = $forUpdate;

        return $this;
    }

    /**
     * Builds a Phalcon\Mvc\Model\Criteria based on an input array like $_POST
     *
     * @param DiInterface $container
     * @param string      $modelName
     * @param array       $data
     * @param string      $operator
     *
     * @return CriteriaInterface
     */
    public static function fromInput(
        DiInterface $container,
        string $modelName,
        array $data,
        string $operator = "AND"
    ): CriteriaInterface {
        $conditions = [];
        $bind       = [];

        if (count($data)) {
            $metaData  = $container->getShared("modelsMetadata");
            $model     = new $modelName(null, $container);
            $dataTypes = $metaData->getDataTypes($model);
            $columnMap = $metaData->getReverseColumnMap($model);

            /**
             * We look for attributes in the array passed as data
             */
            foreach ($data as $field => $value) {
                $attribute = $field;
                if (is_array($columnMap) && count($columnMap)) {
                    $attribute = $columnMap[$field];
                }

                if (
                    isset($dataTypes[$attribute]) &&
                    $value !== null &&
                    $value !== ""
                ) {
                    $type = $dataTypes[$attribute];
                    if ($type == Column::TYPE_VARCHAR) {
                        /**
                         * For varchar types we use LIKE operator
                         */
                        $conditions[] = "[" . $field . "] LIKE :" . $field . ":";
                        $bind[$field] = "%" . $value . "%";

                        continue;
                    }

                    /**
                     * For the rest of data types we use a plain = operator
                     */
                    $conditions[] = "[" . $field . "] = :" . $field . ":";
                    $bind[$field] = $value;
                }
            }
        }

        /**
         * Create an object instance and pass the parameters to it
         */
        $criteria = new self();
        $criteria->setDI($container);

        if (count($conditions)) {
            $criteria->where(
                implode(" " . $operator . " ", $conditions)
            );

            $criteria->bind($bind);
        }

        $criteria->setModelName($modelName);

        return $criteria;
    }

    /**
     * Returns the columns to be queried
     *
     * @return string|array|null
     */
    public function getColumns(): string | array | null
    {
        return $this->params["columns"] ?? null;
    }

    /**
     * Returns the conditions parameter in the criteria
     *
     * @return string|null
     */
    public function getConditions(): string | null
    {
        return $this->getWhere();
    }

    /**
     * Returns the DependencyInjector container
     *
     * @return DiInterface
     */
    public function getDI(): DiInterface
    {
        return $this->params["di"];
    }

    /**
     * Returns the group clause in the criteria
     *
     * @return mixed|null
     */
    public function getGroupBy()
    {
        return $this->params["group"] ?? null;
    }

    /**
     * Returns the having clause in the criteria
     */
    public function getHaving()
    {
        return $this->params["having"] ?? null;
    }

    /**
     * Returns the limit parameter in the criteria, which will be
     *
     * - An integer if 'limit' was set without an 'offset'
     * - An array with 'number' and 'offset' keys if an offset was set with the limit
     * - NULL if limit has not been set
     *
     * @return array|int|null
     */
    public function getLimit(): array | int | null
    {
        return $this->params["limit"] ?? null;
    }

    /**
     * Returns an internal model name on which the criteria will be applied
     *
     * @return string
     */
    public function getModelName(): string
    {
        return $this->model;
    }

    /**
     * Returns the order clause in the criteria
     *
     * @return string|null
     */
    public function getOrderBy(): string | null
    {
        return $this->params["order"] ?? null;
    }

    /**
     * Returns all the parameters defined in the criteria
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns the conditions parameter in the criteria
     *
     * @return string|null
     */
    public function getWhere(): string | null
    {
        return $this->params["conditions"] ?? null;
    }

    /**
     * Adds the group-by clause to the criteria
     *
     * @param mixed $group
     *
     * @return CriteriaInterface
     */
    public function groupBy(mixed $group): CriteriaInterface
    {
        $this->params["group"] = $group;

        return $this;
    }

    /**
     * Adds the having clause to the criteria
     *
     * @param mixed $having
     *
     * @return CriteriaInterface
     */
    public function having(mixed $having): CriteriaInterface
    {
        $this->params["having"] = $having;

        return $this;
    }

    /**
     * Appends an IN condition to the current conditions
     *
     * ```php
     * $criteria->inWhere("id", [1, 2, 3]);
     * ```
     *
     * @param string $expr
     * @param array  $values
     *
     * @return CriteriaInterface
     */
    public function inWhere(string $expr, array $values): CriteriaInterface
    {
        if (!count($values)) {
            $this->andWhere($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = $this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys   = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key = "ACP" . $hiddenParam;

            $queryKey = ":" . $key . ":";

            $bindKeys[]       = $queryKey;
            $bindParams[$key] = $value;

            $hiddenParam++;
        }

        /**
         * Create a standard IN condition with bind params
         * Append the IN to the current conditions using and "and"
         */
        $this->andWhere(
            $expr . " IN (" . implode(", ", $bindKeys) . ")",
            $bindParams
        );

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }

    /**
     * Adds an INNER join to the query
     *
     *```php
     * <?php
     *
     * $criteria->innerJoin(
     *     Invoices::class
     * );
     *
     * $criteria->innerJoin(
     *     Invoices::class,
     *     "inv_cst_id = Customers.cst_id"
     * );
     *
     * $criteria->innerJoin(
     *     Invoices::class,
     *     "i.inv_cst_id = Customers.cst_id",
     *     "i"
     * );
     *```
     *
     * @param string     $model
     * @param mixed|null $conditions
     * @param mixed|null $alias
     *
     * @return CriteriaInterface
     */
    public function innerJoin(
        string $model,
        mixed $conditions = null,
        mixed $alias = null
    ): CriteriaInterface {
        return $this->join($model, $conditions, $alias, "INNER");
    }

    /**
     * Adds an INNER join to the query
     *
     *```php
     * <?php
     *
     * $criteria->join(
     *     Invoices::class
     * );
     *
     * $criteria->join(
     *     Invoices::class,
     *     "inv_cst_id = Customers.cst_id"
     * );
     *
     * $criteria->join(
     *     Invoices::class,
     *     "i.inv_cst_id = Customers.cst_id",
     *     "i"
     * );
     *
     * $criteria->join(
     *     Invoices::class,
     *     "i.inv_cst_id = Customers.cst_id",
     *     "i",
     *     "LEFT"
     * );
     *```
     *
     * @param string     $model
     * @param mixed|null $conditions
     * @param mixed|null $alias
     * @param mixed|null $type
     *
     * @return CriteriaInterface
     */
    public function join(
        string $model,
        mixed $conditions = null,
        mixed $alias = null,
        mixed $type = null
    ): CriteriaInterface {
        $join = [$model, $conditions, $alias, $type];

        if (isset($this->params["joins"])) {
            $currentJoins = $this->params["joins"];
            if (is_array($currentJoins)) {
                $mergedJoins = array_merge($currentJoins, [$join]);
            } else {
                $mergedJoins = [$join];
            }
        } else {
            $mergedJoins = [$join];
        }

        $this->params["joins"] = $mergedJoins;

        return $this;
    }

    /**
     * Adds a LEFT join to the query
     *
     *```php
     * <?php
     *
     * $criteria->leftJoin(
     *     Invoices::class,
     *     "i.inv_cst_id = Customers.cst_id",
     *     "i"
     * );
     *```
     *
     * @param string     $model
     * @param mixed|null $conditions
     * @param mixed|null $alias
     *
     * @return CriteriaInterface
     */
    public function leftJoin(
        string $model,
        mixed $conditions = null,
        mixed $alias = null
    ): CriteriaInterface {
        return $this->join($model, $conditions, $alias, "LEFT");
    }

    /**
     * Adds the limit parameter to the criteria.
     *
     * ```php
     * $criteria->limit(100);
     * $criteria->limit(100, 200);
     * $criteria->limit("100", "200");
     * ```
     *
     * @param int $limit
     * @param int $offset
     *
     * @return CriteriaInterface
     */
    public function limit(int $limit, int $offset = 0): CriteriaInterface
    {
        $limit  = abs($limit);
        $offset = abs($offset);

        if ($limit == 0) {
            return $this;
        }

        if ($offset == 0) {
            $this->params["limit"] = $limit;
        } else {
            $this->params["limit"] = [
                "number" => $limit,
                "offset" => $offset,
            ];
        }

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition to the current conditions
     *
     *```php
     * $criteria->notBetweenWhere("price", 100.25, 200.50);
     *```
     */
    public function notBetweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum
    ): CriteriaInterface {
        $hiddenParam     = $this->hiddenParamNumber;
        $nextHiddenParam = $hiddenParam + 1;

        /**
         * Minimum key with auto bind-params
         */
        $minimumKey = "ACP" . $hiddenParam;

        /**
         * Maximum key with auto bind-params
         */
        $maximumKey = "ACP" . $nextHiddenParam;

        /**
         * Create a standard BETWEEN condition with bind params
         * Append the BETWEEN to the current conditions using and "and"
         */
        $this->andWhere(
            $expr . " NOT BETWEEN :" . $minimumKey . ": AND :" . $maximumKey . ":",
            [
                $minimumKey => $minimum,
                $maximumKey => $maximum,
            ]
        );

        $nextHiddenParam++;

        $this->hiddenParamNumber = $nextHiddenParam;

        return $this;
    }

    /**
     * Appends a NOT IN condition to the current conditions
     *
     *```php
     * $criteria->notInWhere("id", [1, 2, 3]);
     *```
     *
     * @param string $expr
     * @param array  $values
     *
     * @return CriteriaInterface
     */
    public function notInWhere(string $expr, array $values): CriteriaInterface
    {
        $hiddenParam = $this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys   = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key              = "ACP" . $hiddenParam;
            $bindKeys[]       = ":" . $key . ":";
            $bindParams[$key] = $value;

            $hiddenParam++;
        }

        /**
         * Create a standard IN condition with bind params
         * Append the IN to the current conditions using and "and"
         */
        $this->andWhere(
            $expr . " NOT IN (" . implode(", ", $bindKeys) . ")",
            $bindParams
        );

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }

    /**
     * Appends a condition to the current conditions using an OR operator
     *
     * @param string     $conditions
     * @param array|null $bindParams
     * @param array|null $bindTypes
     *
     * @return CriteriaInterface
     */
    public function orWhere(
        string $conditions,
        array | null $bindParams = null,
        array | null $bindTypes = null
    ): CriteriaInterface {
        if (isset($this->params["conditions"])) {
            $conditions = "(" . $this->params["conditions"] . ") OR (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Adds the order-by clause to the criteria
     *
     * @param string $orderColumns
     *
     * @return CriteriaInterface
     */
    public function orderBy(string $orderColumns): CriteriaInterface
    {
        $this->params["order"] = $orderColumns;

        return $this;
    }

    /**
     * Adds a RIGHT join to the query
     *
     *```php
     * <?php
     *
     * $criteria->rightJoin(
     *     Invoices::class,
     *     "i.inv_cst_id = Customers.cst_id",
     *     "i"
     * );
     *```
     *
     * @param string     $model
     * @param mixed|null $conditions
     * @param mixed|null $alias
     *
     * @return CriteriaInterface
     */
    public function rightJoin(string $model, mixed $conditions = null, mixed $alias = null): CriteriaInterface
    {
        return $this->join($model, $conditions, $alias, "RIGHT");
    }

    /**
     * Sets the DependencyInjector container
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public function setDI(DiInterface $container): void
    {
        $this->params["di"] = $container;
    }

    /**
     * Set a model on which the query will be executed
     *
     * @param string $modelName
     *
     * @return CriteriaInterface
     */
    public function setModelName(string $modelName): CriteriaInterface
    {
        $this->model = $modelName;

        return $this;
    }

    /**
     * Adds the "shared_lock" parameter to the criteria
     *
     * @param bool $sharedLock
     *
     * @return CriteriaInterface
     */
    public function sharedLock(bool $sharedLock = true): CriteriaInterface
    {
        $this->params["shared_lock"] = $sharedLock;

        return $this;
    }

    /**
     * Sets the conditions parameter in the criteria
     *
     * @param string     $conditions
     * @param array|null $bindParams
     * @param array|null $bindTypes
     *
     * @return CriteriaInterface
     */
    public function where(
        string $conditions,
        array | null $bindParams = null,
        array | null $bindTypes = null
    ): CriteriaInterface {
        $this->params["conditions"] = $conditions;

        /**
         * Update or merge existing bound parameters
         */
        if (is_array($bindParams)) {
            if (isset($this->params["bind"])) {
                $this->params["bind"] = array_merge(
                    $this->params["bind"],
                    $bindParams
                );
            } else {
                $this->params["bind"] = $bindParams;
            }
        }

        /**
         * Update or merge existing bind types parameters
         */
        if (is_array($bindTypes)) {
            if (isset($this->params["bindTypes"])) {
                $this->params["bindTypes"] = array_merge(
                    $this->params["bindTypes"],
                    $bindTypes
                );
            } else {
                $this->params["bindTypes"] = $bindTypes;
            }
        }

        return $this;
    }
}
