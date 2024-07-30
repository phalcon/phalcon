<?php

/*
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc\Model\Query;

use Phalcon\Di\Di;
use Phalcon\Db\Column;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\QueryInterface;

/**
 * Phalcon\Mvc\Model\Query\Builder
 * Helps to create PHQL queries using an OO interface
 *```php
 * $params = [
 *     "models"     => [
 *         Users::class,
 *     ],
 *     "columns"    => ["id", "name", "status"],
 *     "conditions" => [
 *         [
 *             "created > :min: AND created < :max:",
 *             [
 *                 "min" => "2013-01-01",
 *                 "max" => "2014-01-01",
 *             ],
 *             [
 *                 "min" => PDO::PARAM_STR,
 *                 "max" => PDO::PARAM_STR,
 *             ],
 *         ],
 *     ],
 *     // or "conditions" => "created > '2013-01-01' AND created < '2014-01-01'",
 *     "group"      => ["id", "name"],
 *     "having"     => "name = 'Kamil'",
 *     "order"      => ["name", "id"],
 *     "limit"      => 20,
 *     "offset"     => 20,
 *     // or "limit" => [20, 20],
 * ];
 * $queryBuilder = new \Phalcon\Mvc\Model\Query\Builder($params);
 *```
 */
class Builder implements BuilderInterface, InjectionAwareInterface
{
    /**
     * @var array
     */
    protected array $bindParams = [];

    /**
     * @var array
     */
    protected array $bindTypes = [];

    /**
     * @var array|string|null
     */
    protected array | string | null $columns = null;

    /**
     * @var array|string|null
     */
    protected array | string | null $conditions = null;

    /**
     * @var DiInterface|null
     */
    protected ?DiInterface $container;

    /**
     * @var mixed
     */
    protected mixed $distinct = null;

    /**
     * @var bool
     */
    protected bool $forUpdate = false;

    /**
     * @var array
     */
    protected array $group = [];

    /**
     * @var string|null
     */
    protected string | null $having = null;

    /**
     * @var int
     */
    protected int $hiddenParamNumber = 0;

    /**
     * @var array
     */
    protected array $joins = [];

    /**
     * @var array|int|string
     */
    protected array | int | string $limit = 0;

    /**
     * @var array|string|null
     */
    protected array | string | null $models;

    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * @var array|string
     */
    protected array | string $order = [];

    /**
     * @var bool
     */
    protected bool $sharedLock = false;

    /**
     * Phalcon\Mvc\Model\Query\Builder constructor
     *
     * @param array|string|null $params
     * @param DiInterface|null $container
     */
    public function __construct(array | string | null $params = null, DiInterface | null $container = null)
    {
        if (is_array($params)) {
            /**
             * Process conditions
             */
            $this->conditions = $params[0] ?? $params['conditions'] ?? [];
        }

        if (is_array($this->conditions)) {
            $mergedConditions = [];
            $mergedParams = [];
            $mergedTypes = [];

            foreach ($this->conditions as $singleConditionArray) {
                if (is_array($singleConditionArray)) {
                    if (isset($singleConditionArray[0]) && is_string($singleConditionArray[0])) {
                        $mergedConditions[] = $singleConditionArray[0];
                    }

                    if (isset($singleConditionArray[1]) && is_array($singleConditionArray[1])) {
                        $mergedParams = $mergedParams + $singleConditionArray[1];
                    }

                    if (isset($singleConditionArray[2]) && is_array($singleConditionArray[2])) {
                        $mergedTypes = $mergedTypes + $singleConditionArray[2];
                    }
                }
            }

            $this->conditions = implode(" AND ", $mergedConditions);

            $this->bindParams = $mergedParams;
            $this->bindTypes = $mergedTypes;

            /**
             * Assign bind types
             */
            if (isset($params["bind"])) {
                $this->bindParams = $params['bind'];
            }

            if (isset($params["bindTypes"])) {
                $this->bindTypes = $params['bindTypes'];
            }

            /**
             * Assign SELECT DISTINCT / SELECT ALL clause
             */
            if (isset($params["distinct"])) {
                $this->distinct = $params['distinct'];
            }

            /**
             * Assign FROM clause
             */
            if (isset($params["models"])) {
                $this->models = $params['models'];
            }

            /**
             * Assign COLUMNS clause
             */
            if (isset($params["columns"])) {
                $this->columns = $params['columns'];
            }

            /**
             * Assign JOIN clause
             */
            if (isset($params["joins"])) {
                $this->joins = $params['joins'];
            }

            /**
             * Assign GROUP clause
             */
            if (isset($params["bind"])) {
                $this->groupBy($params['group']);
            }

            /**
             * Assign HAVING clause
             */
            if (isset($params["having"])) {
                $this->having = $params['having'];
            }

            /**
             * Assign ORDER clause
             */
            if (isset($params["order"])) {
                $this->order = $params['order'];
            }

            /**
             * Assign LIMIT clause
             */
            if (isset($params["limit"])) {
                if (is_array($params['limit'])) {
                    if (isset($params['limit'][0]) && is_int($params['limit'][0])) {
                        $this->limit = $params['limit'][0];
                    }
                    if (isset($params['limit'][1]) && is_int($params['limit'][1])) {
                        $this->offset = $params['limit'][1];
                    }
                } else {
                    $this->limit = $params['limit'];
                }
            }

            /**
             * Assign OFFSET clause
             */
            if (isset($params["offset"])) {
                $this->offset = $params['offset'];
            }

            /**
             * Assign FOR UPDATE clause
             */
            if (isset($params["for_update"])) {
                $this->forUpdate = $params['for_update'];
            }

            /**
             * Assign SHARED LOCK clause
             */
            if (isset($params["shared_lock"])) {
                $this->sharedLock = $params['shared_lock'];
            }
        } else {
            if (is_string($params) && $params !== '') {
                $this->conditions = $params;
            }
        }

        /**
         * Update the dependency injector if any
         */
        $this->container = $container;
    }

    /**
     * Add a model to take part of the query
     *```php
     * // Load data from models Robots
     * $builder->addFrom(
     *     Robots::class
     * );
     * // Load data from model 'Robots' using 'r' as alias in PHQL
     * $builder->addFrom(
     *     Robots::class,
     *     "r"
     * );
     *```
     */
    public function addFrom(string $model, string $alias = null): BuilderInterface
    {
        $models = $this->models;

        if (!is_array($models)) {
            if ($models !== null) {
                $currentModel = $models;
                $models = [$currentModel];
            } else {
                $models = [];
            }
        }

        if (is_string($alias) && $alias !== '') {
            $models[$alias] = $model;
        } else {
            $models[] = $model;
        }

        $this->models = $models;

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using a AND operator
     *```php
     * $builder->andHaving("SUM(Robots.price) > 0");
     * $builder->andHaving(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     */
    public function andHaving(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $currentConditions = $this->having;

        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }
        return $this->having($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a condition to the current WHERE conditions using a AND operator
     *```php
     * $builder->andWhere("name = 'Peter'");
     * $builder->andWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     *```
     */
    public function andWhere(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $currentConditions = $this->conditions;

        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Automatically escapes identifiers but only if they need to be escaped.
     */
    final public function autoescape(string $identifier): string
    {
        if (str_contains($identifier, "[") || str_contains($identifier, ".") || is_numeric($identifier)) {
            return $identifier;
        }

        return "[" . $identifier . "]";
    }

    /**
     * Appends a BETWEEN condition to the current HAVING conditions clause
     *```php
     * $builder->betweenHaving("SUM(Robots.price)", 100.25, 200.50);
     *```
     */
    public function betweenHaving(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionBetween("Having", $operator, $expr, $minimum, $maximum);
    }

    /**
     * Appends a BETWEEN condition to the current WHERE conditions
     *```php
     * $builder->betweenWhere("price", 100.25, 200.50);
     *```
     */
    public function betweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionBetween("Where", $operator, $expr, $minimum, $maximum);
    }

    /**
     * Sets the columns to be queried. The columns can be either a `string` or
     * an `array` of strings. If the argument is a (single, non-embedded) string,
     * its content can specify one or more columns, separated by commas, the same
     * way that one uses the SQL select statement. You can use aliases, aggregate
     * functions, etc. If you need to reference other models you will need to
     * reference them with their namespaces.
     * When using an array as a parameter, you will need to specify one field
     * per array element. If a non-numeric key is defined in the array, it will
     * be used as the alias in the query
     *```php
     * <?php
     * // String, comma separated values
     * $builder->columns("id, category");
     * // Array, one column per element
     * $builder->columns(
     *     [
     *         "inv_id",
     *         "inv_total",
     *     ]
     * );
     * // Array with named key. The name of the key acts as an
     * // alias (`AS` clause)
     * $builder->columns(
     *     [
     *         "inv_cst_id",
     *         "total_invoices" => "COUNT(*)",
     *     ]
     * );
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
     * @param string|array $columns
     */
    public function columns(string | array $columns): BuilderInterface
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets SELECT DISTINCT / SELECT ALL flag
     *```php
     * $builder->distinct("status");
     * $builder->distinct(null);
     *```
     */
    public function distinct(mixed $distinct): BuilderInterface
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Sets a FOR UPDATE clause
     *```php
     * $builder->forUpdate(true);
     *```
     */
    public function forUpdate(bool $forUpdate): BuilderInterface
    {
        $this->forUpdate = $forUpdate;

        return $this;
    }

    /**
     * Sets the models who makes part of the query
     *```php
     * $builder->from(
     *     Robots::class
     * );
     * $builder->from(
     *     [
     *         Robots::class,
     *         RobotsParts::class,
     *     ]
     * );
     * $builder->from(
     *     [
     *         "r"  => Robots::class,
     *         "rp" => RobotsParts::class,
     *     ]
     * );
     *```
     *
     * @param string|array $models
     *
     * @return BuilderInterface
     */
    public function from(string | array $models): BuilderInterface
    {
        $this->models = $models;

        return $this;
    }

    /**
     * Returns default bind params
     */
    public function getBindParams(): array
    {
        return $this->bindParams;
    }

    /**
     * Returns default bind types
     */
    public function getBindTypes(): array
    {
        return $this->bindTypes;
    }

    /**
     * Return the columns to be queried
     *
     * @return array|string
     */
    public function getColumns(): array | string
    {
        return $this->columns ?? [];
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI(): DiInterface | null
    {
        return $this->container;
    }

    /**
     * Returns SELECT DISTINCT / SELECT ALL flag
     */
    public function getDistinct(): bool
    {
        return $this->distinct;
    }

    /**
     * Return the models who makes part of the query
     *
     * @return array|string
     */
    public function getFrom(): array | string
    {
        return $this->models ?? [];
    }

    /**
     * Returns the GROUP BY clause
     */
    public function getGroupBy(): array
    {
        return $this->group;
    }

    /**
     * Return the current having clause
     */
    public function getHaving(): string
    {
        return (string)$this->having;
    }

    /**
     * Return join parts of the query
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * Returns the current LIMIT clause
     *
     * @return array|int|string
     */
    public function getLimit(): array | int | string
    {
        return $this->limit;
    }

    /**
     * Returns the models involved in the query
     */
    public function getModels(): string | array | null
    {
        $models = $this->models;

        if (is_array($models) && count($models) === 1) {
            return reset($models);
        }

        return $models;
    }

    /**
     * Returns the current OFFSET clause
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Returns the set ORDER BY clause
     *
     * @return array|string
     */
    public function getOrderBy(): array | string
    {
        return $this->order;
    }

    /**
     * Returns a PHQL statement built based on the builder parameters
     */
    final public function getPhql(): string
    {
        $container = $this->container;
        if (!is_object($container)) {
            $container = Di::getDefault();
            if ($container === null) {
                throw new Exception('Di container required');
            }
            $this->container = $container;
        }

        $models = $this->models;
        if (is_array($models)) {
            if (!count($models)) {
                throw new Exception(
                    "At least one model is required to build the query"
                );
            }
        } else {
            if (!$models) {
                throw new Exception(
                    "At least one model is required to build the query"
                );
            }
        }

        $conditions = $this->conditions;

        if (is_numeric($conditions)) {
            /**
             * If the conditions is a single numeric field. We internally create
             * a condition using the related primary key
             */
            if (is_array($models)) {
                if (count($models) > 1) {
                    throw new Exception(
                        "Cannot build the query. Invalid condition"
                    );
                }

                $model = $models[0];
            } else {
                $model = $models;
            }

            /**
             * Get the models metadata service to obtain the column names,
             * column map and primary key
             */
            $metaData = $container->getShared("modelsMetadata");
            $modelInstance = new $model(null, $container);

            $noPrimary = true;
            $primaryKeys = $metaData->getPrimaryKeyAttributes($modelInstance);

            if (count($primaryKeys)) {
                if (isset($primaryKeys[0])) {
                    $firstPrimaryKey = $primaryKeys[0];
                    /**
                     * The PHQL contains the renamed columns if available
                     */
                    if (ini_get("phalcon.orm.column_renaming")) {
                        $columnMap = $metaData->getColumnMap($modelInstance);
                    } else {
                        $columnMap = null;
                    }

                    if (is_array($columnMap)) {
                        if (!isset($columnMap[$firstPrimaryKey])) {
                            throw new Exception(
                                "Column '" . $firstPrimaryKey . "' isn't part of the column map"
                            );
                        }
                        $attributeField = $columnMap[$firstPrimaryKey];
                    } else {
                        $attributeField = $firstPrimaryKey;
                    }

                    // check the type of the condition, if it's a string put single quotes around the value
                    if (!empty($conditions)) {
                        /*
                         * Example : if the developer writes findFirstBy('135'), Phalcon will generate where uuid = 135.
                         * But the column's type is text so Postgres needs to have single quotes such as ;
                         * where uuid = '135'.
                         */
                        $conditions = "'" . $conditions . "'";
                    }

                    $conditions = $this->autoescape($model) .
                        "." .
                        $this->autoescape($attributeField) .
                        " = " .
                        $conditions;
                    $noPrimary = false;
                }
            }

            /**
             * A primary key is mandatory in these cases
             */
            if ($noPrimary) {
                throw new Exception(
                    "Source related to this model does not have a primary key defined"
                );
            }
        }

        $distinct = $this->distinct;

        if (is_bool($distinct)) {
            if ($distinct) {
                $phql = "SELECT DISTINCT ";
            } else {
                $phql = "SELECT ALL ";
            }
        } else {
            $phql = "SELECT ";
        }

        $columns = $this->columns;

        if ($columns !== null) {
            /**
             * Generate PHQL for columns
             */
            if (is_array($columns)) {
                $selectedColumns = [];

                foreach ($columns as $columnAlias => $column) {
                    if (is_numeric($columnAlias)) {
                        $selectedColumns[] = $column;
                    } else {
                        $selectedColumns[] = $column . " AS " . $this->autoescape($columnAlias);
                    }
                }

                $phql .= join(", ", $selectedColumns);
            } else {
                $phql .= $columns;
            }
        } else {
            /**
             * Automatically generate an array of models
             */
            if (is_array($models)) {
                $selectedColumns = [];

                foreach ($models as $modelColumnAlias => $model) {
                    if (is_numeric($modelColumnAlias)) {
                        $selectedColumn = $this->autoescape($model) . ".*";
                    } else {
                        $selectedColumn = $this->autoescape($modelColumnAlias) . ".*";
                    }

                    $selectedColumns[] = $selectedColumn;
                }

                $phql .= join(", ", $selectedColumns);
            } else {
                $phql .= $this->autoescape($models) . ".*";
            }
        }

        /**
         * Join multiple models or use a single one if it is a string
         */
        if (is_array($models)) {
            $selectedModels = [];

            foreach ($models as $modelAlias => $model) {
                if (is_string($modelAlias)) {
                    $selectedModel = $this->autoescape($model) . " AS " . $this->autoescape($modelAlias);
                } else {
                    $selectedModel = $this->autoescape($model);
                }

                $selectedModels[] = $selectedModel;
            }

            $phql .= " FROM " . join(", ", $selectedModels);
        } else {
            $phql .= " FROM " . $this->autoescape($models);
        }

        /**
         * Check if joins were passed to the builders
         */
        foreach ($this->joins as $join) {
            /**
             * The joined table is in the first place of the array
             */
            $joinModel = $join[0];

            /**
             * The join conditions are in the second place of the array
             */
            $joinConditions = $join[1];

            /**
             * The join alias is in the second place of the array
             */
            $joinAlias = $join[2];

            /**
             * Join type
             */
            $joinType = $join[3];

            /**
             * Create the join according to the type
             */
            if ($joinType) {
                $phql .= " " . $joinType . " JOIN " . $this->autoescape($joinModel);
            } else {
                $phql .= " JOIN " . $this->autoescape($joinModel);
            }

            /**
             * Alias comes first
             */
            if ($joinAlias) {
                $phql .= " AS " . $this->autoescape($joinAlias);
            }

            /**
             * Conditions then
             */
            if ($joinConditions) {
                $phql .= " ON " . $joinConditions;
            }
        }

        // Only append where conditions if it's string
        if (is_string($conditions)) {
            if (trim($conditions) !== '') {
                $phql .= " WHERE " . $conditions;
            }
        }

        /**
         * Process group parameters
         */
        $group = $this->group;
        if (!empty($group)) {
            $groupItems = [];

            foreach ($group as $groupItem) {
                $groupItems[] = $this->autoescape($groupItem);
            }

            $phql .= " GROUP BY " . join(", ", $groupItems);
        }

        /**
         * Process having clause
         */
        $having = $this->having;
        if ($having !== null) {
            if (trim($having) !== '') {
                $phql .= " HAVING " . $having;
            }
        }

        /**
         * Process order clause
         */
        if ($this->order) {
            if (is_array($this->order)) {
                $orderItems = [];

                foreach ($this->order as $orderItem) {
                    /**
                     * For case 'ORDER BY 1'
                     */
                    if (is_int($orderItem)) {
                        $orderItems[] = $orderItem;

                        continue;
                    }

                    if (str_contains($orderItem, " ")) {
                        $itemExplode = explode(" ", $orderItem);
                        $orderItems[] = $this->autoescape($itemExplode[0]) . " " . $itemExplode[1];

                        continue;
                    }

                    $orderItems[] = $this->autoescape($orderItem);
                }

                $phql .= " ORDER BY " . join(", ", $orderItems);
            } else {
                $phql .= " ORDER BY " . $this->order;
            }
        }

        /**
         * Process limit parameters
         */
        $limit = $this->limit;
        if (!empty($limit)) {
            $number = null;
            $offset = 0;

            if (is_array($limit)) {
                $number = $limit["number"];

                if (isset($limit['offset'])) {
                    $offset = !is_numeric($limit['offset']) ? 0 : (int)$limit['offset'];
                }
            } else {
                if (is_numeric($limit)) {
                    $number = $limit;
                    $offset = $this->offset;
                    if ($offset <= 0) {
                        $offset = 0;
                    }
                }
            }

            if (is_numeric($number)) {
                $phql .= " LIMIT :APL0:";
                $this->bindParams["APL0"] = intval($number, 10);
                $this->bindTypes["APL0"] = Column::BIND_PARAM_INT;

                if ($offset !== 0) {
                    $phql .= " OFFSET :APL1:";
                    $this->bindParams["APL1"] = intval($offset, 10);
                    $this->bindTypes["APL1"] = Column::BIND_PARAM_INT;
                }
            }
        }

        if ($this->forUpdate) {
            $phql .= " FOR UPDATE";
        }

        return $phql;
    }

    /**
     * Returns the query built
     *
     * @throws Exception
     * @return QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        $phql = $this->getPhql();

        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                "A dependency injection container is required to access the services related to the ORM"
            );
        }

        /**
         * Gets Query instance from DI container
         */
        $query = $container->get(
            "Phalcon\\Mvc\\Model\\Query",
            [$phql, $container]
        );

        // Set default bind params
        $query->setBindParams($this->bindParams);

        // Set default bind types
        $query->setBindTypes($this->bindTypes);

        $query->setSharedLock($this->sharedLock);

        return $query;
    }

    /**
     * Return the conditions for the query
     *
     * @return string | null
     */
    public function getWhere(): string | null
    {
        return $this->conditions;
    }

    /**
     * Sets a GROUP BY clause
     *```php
     * $builder->groupBy(
     *     [
     *         "Robots.name",
     *     ]
     * );
     *```
     *
     * @param array|string $group
     *
     * @return BuilderInterface
     */
    public function groupBy(array | string $group): BuilderInterface
    {
        if (is_string($group)) {
            if (str_contains($group, ",")) {
                $group = str_replace(" ", "", $group);
            }

            $group = explode(",", $group);
        }

        $this->group = $group;

        return $this;
    }

    /**
     * Sets the HAVING condition clause
     *```php
     * $builder->having("SUM(Robots.price) > 0");
     * $builder->having(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     */
    public function having(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $this->having = $conditions;

        /**
         * Merge the bind params to the current ones
         */
        $this->bindParams = $this->bindParams + $bindParams;

        /**
         * Merge the bind types to the current ones
         */
        $this->bindTypes = $this->bindTypes + $bindTypes;

        return $this;
    }

    /**
     * Appends an IN condition to the current HAVING conditions clause
     *```php
     * $builder->inHaving("SUM(Robots.price)", [100, 200]);
     *```
     */
    public function inHaving(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionIn("Having", $operator, $expr, $values);
    }

    /**
     * Adds an INNER join to the query
     *```php
     * // Inner Join model 'Robots' with automatic conditions and alias
     * $builder->innerJoin(
     *     Robots::class
     * );
     * // Inner Join model 'Robots' specifying conditions
     * $builder->innerJoin(
     *     Robots::class,
     *     "Robots.id = RobotsParts.robots_id"
     * );
     * // Inner Join model 'Robots' specifying conditions and alias
     * $builder->innerJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     *```
     */
    public function innerJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "INNER"];

        return $this;
    }

    /**
     * Appends an IN condition to the current WHERE conditions
     *```php
     * $builder->inWhere(
     *     "id",
     *     [1, 2, 3]
     * );
     *```
     */
    public function inWhere(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionIn("Where", $operator, $expr, $values);
    }

    /**
     * Adds an :type: join (by default type - INNER) to the query
     *```php
     * // Inner Join model 'Robots' with automatic conditions and alias
     * $builder->join(
     *     Robots::class
     * );
     * // Inner Join model 'Robots' specifying conditions
     * $builder->join(
     *     Robots::class,
     *     "Robots.id = RobotsParts.robots_id"
     * );
     * // Inner Join model 'Robots' specifying conditions and alias
     * $builder->join(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     * // Left Join model 'Robots' specifying conditions, alias and type of join
     * $builder->join(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r",
     *     "LEFT"
     * );
     *```
     */
    public function join(
        string $model,
        string | null $conditions = null,
        string | null $alias = null,
        string | null $type = null
    ): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, $type];

        return $this;
    }

    /**
     * Adds a LEFT join to the query
     *```php
     * $builder->leftJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     *```
     */
    public function leftJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "LEFT"];

        return $this;
    }

    /**
     * Sets a LIMIT clause, optionally an offset clause
     * ```php
     * $builder->limit(100);
     * $builder->limit(100, 20);
     * $builder->limit("100", "20");
     * ```
     */
    public function limit(int $limit, string | int | null $offset = null): BuilderInterface
    {
        $limit = abs($limit);

        if ($limit === 0) {
            return $this;
        }

        $this->limit = (string)$limit;

        if (is_numeric($offset)) {
            $this->offset = (int)abs($offset);
        }

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition to the current HAVING conditions clause
     *```php
     * $builder->notBetweenHaving("SUM(Robots.price)", 100.25, 200.50);
     *```
     */
    public function notBetweenHaving(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionNotBetween(
            "Having",
            $operator,
            $expr,
            $minimum,
            $maximum
        );
    }

    /**
     * Appends a NOT BETWEEN condition to the current WHERE conditions
     *```php
     * $builder->notBetweenWhere("price", 100.25, 200.50);
     *```
     */
    public function notBetweenWhere(
        string $expr,
        mixed $minimum,
        mixed $maximum,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionNotBetween(
            "Where",
            $operator,
            $expr,
            $minimum,
            $maximum
        );
    }

    /**
     * Appends a NOT IN condition to the current HAVING conditions clause
     *```php
     * $builder->notInHaving("SUM(Robots.price)", [100, 200]);
     *```
     */
    public function notInHaving(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionNotIn("Having", $operator, $expr, $values);
    }

    /**
     * Appends a NOT IN condition to the current WHERE conditions
     *```php
     * $builder->notInWhere("id", [1, 2, 3]);
     *```
     */
    public function notInWhere(
        string $expr,
        array $values,
        string $operator = BuilderInterface::OPERATOR_AND
    ): BuilderInterface {
        return $this->conditionNotIn("Where", $operator, $expr, $values);
    }

    /**
     * Sets an OFFSET clause
     *```php
     * $builder->offset(30);
     *```
     */
    public function offset(int $offset): BuilderInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Sets an ORDER BY condition clause
     *```php
     * $builder->orderBy("Robots.name");
     * $builder->orderBy(["1", "Robots.name"]);
     * $builder->orderBy(["Robots.name DESC"]);
     *```
     *
     * @param array|string $orderBy
     */
    public function orderBy(array | string $orderBy): BuilderInterface
    {
        $this->order = $orderBy;

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using an OR operator
     *```php
     * $builder->orHaving("SUM(Robots.price) > 0");
     * $builder->orHaving(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     */
    public function orHaving(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $currentConditions = $this->having;

        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") OR (" . $conditions . ")";
        }

        return $this->having($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a condition to the current conditions using an OR operator
     *```php
     * $builder->orWhere("name = 'Peter'");
     * $builder->orWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     *```
     */
    public function orWhere(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $currentConditions = $this->conditions;

        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") OR (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Adds a RIGHT join to the query
     *```php
     * $builder->rightJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     *```
     */
    public function rightJoin(
        string $model,
        string | null $conditions = null,
        string | null $alias = null
    ): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "RIGHT"];

        return $this;
    }

    /**
     * Set default bind parameters
     */
    public function setBindParams(array $bindParams, bool $merge = false): BuilderInterface
    {
        if ($merge) {
            $this->bindParams = $this->bindParams + $bindParams;
        } else {
            $this->bindParams = $bindParams;
        }

        return $this;
    }

    /**
     * Set default bind types
     */
    public function setBindTypes(array $bindTypes, bool $merge = false): BuilderInterface
    {
        if ($merge) {
            $this->bindTypes = $this->bindTypes + $bindTypes;
        } else {
            $this->bindTypes = $bindTypes;
        }

        return $this;
    }

    /**
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Sets the query WHERE conditions
     *```php
     * $builder->where(100);
     * $builder->where("name = 'Peter'");
     * $builder->where(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     *```
     */
    public function where(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface
    {
        $this->conditions = $conditions;

        /**
         * Merge the bind params to the current ones
         */
        if (count($bindParams) > 0) {
            $this->bindParams = $this->bindParams + $bindParams;
        }

        /**
         * Merge the bind types to the current ones
         */
        if (count($bindTypes) > 0) {
            $this->bindTypes = $this->bindTypes + $bindTypes;
        }

        return $this;
    }

    /**
     * Appends a BETWEEN condition
     */
    protected function conditionBetween(
        string $clause,
        string $operator,
        string $expr,
        mixed $minimum,
        mixed $maximum
    ): BuilderInterface {
        if ($operator !== self::OPERATOR_AND && $operator !== self::OPERATOR_OR) {
            throw new Exception(
                sprintf(
                    "Operator %s is not available.",
                    $operator
                )
            );
        }

        $operatorMethod = $operator . $clause;

        $hiddenParam = $this->hiddenParamNumber;
        $nextHiddenParam = $hiddenParam + 1;

        /**
         * Minimum key with auto bind-params and
         * Maximum key with auto bind-params
         */
        $minimumKey = "AP" . $hiddenParam;
        $maximumKey = "AP" . $nextHiddenParam;

        /**
         * Create a standard BETWEEN condition with bind params
         * Append the BETWEEN to the current conditions using and "and"
         */

        $this->$operatorMethod(
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
     * Appends an IN condition
     */
    protected function conditionIn(string $clause, string $operator, string $expr, array $values): BuilderInterface
    {
        if ($operator !== self::OPERATOR_AND && $operator !== self::OPERATOR_OR) {
            throw new Exception(
                sprintf(
                    "Operator %s is not available.",
                    $operator
                )
            );
        }

        $operatorMethod = $operator . $clause;

        if (!count($values)) {
            $this->$operatorMethod($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = $this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key = "AP" . $hiddenParam;
            $queryKey = ":" . $key . ":";
            $bindKeys[] = $queryKey;
            $bindParams[$key] = $value;
            $hiddenParam++;
        }

        /**
         * Create a standard IN condition with bind params
         * Append the IN to the current conditions using and "and"
         */
        $this->$operatorMethod(
            $expr . " IN (" . join(", ", $bindKeys) . ")",
            $bindParams
        );

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition
     */
    protected function conditionNotBetween(
        string $clause,
        string $operator,
        string $expr,
        mixed $minimum,
        mixed $maximum
    ): BuilderInterface {
        if ($operator !== self::OPERATOR_AND && $operator !== self::OPERATOR_OR) {
            throw new Exception(
                sprintf(
                    "Operator %s is not available.",
                    $operator
                )
            );
        }

        $operatorMethod = $operator . $clause;

        $hiddenParam = $this->hiddenParamNumber;
        $nextHiddenParam = $hiddenParam + 1;

        /**
         * Minimum key with auto bind-params and
         * Maximum key with auto bind-params
         */
        $minimumKey = "AP" . $hiddenParam;
        $maximumKey = "AP" . $nextHiddenParam;

        /**
         * Create a standard BETWEEN condition with bind params
         * Append the NOT BETWEEN to the current conditions using and "and"
         */
        $this->$operatorMethod(
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
     * Appends a NOT IN condition
     */
    protected function conditionNotIn(string $clause, string $operator, string $expr, array $values): BuilderInterface
    {
        if ($operator !== self::OPERATOR_AND && $operator !== self::OPERATOR_OR) {
            throw new Exception(
                sprintf(
                    "Operator %s is not available.",
                    $operator
                )
            );
        }

        $operatorMethod = $operator . $clause;

        if (!count($values)) {
            $this->$operatorMethod($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = $this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key = "AP" . $hiddenParam;
            $queryKey = ":" . $key . ":";
            $bindKeys[] = $queryKey;
            $bindParams[$key] = $value;
            $hiddenParam++;
        }

        /**
         * Create a standard NOT IN condition with bind params
         * Append the NOT IN to the current conditions using and "and"
         */
        $this->$operatorMethod($expr . " NOT IN (" . join(", ", $bindKeys) . ")", $bindParams);

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }
}
