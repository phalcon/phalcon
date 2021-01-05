<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\Model\Query;

use Phalcon\Di;
use Phalcon\Db\Column;
use Phalcon\Di\DiInterface;
use Phalcon\Helper\Arr;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\QueryInterface;
use Phalcon\Reflect\Create;

/**
 * Phalcon\Mvc\Model\Query\Builder
 *
 * Helps to create PHQL queries using an OO interface
 *
 * ```php
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
 *
 * $queryBuilder = new \Phalcon\Mvc\Model\Query\Builder($params);
 * ```
 */
class SqlBuilder implements BuilderInterface, InjectionAwareInterface {

    protected $bindParams;
    protected $bindTypes;
    protected $columns;
    protected $conditions;
    protected $container;
    protected $distinct;
    protected $forUpdate;

    /**
     * @var array
     */
    protected $group;
    protected $having;
    protected $hiddenParamNumber = 0;
    protected $joins;
    protected $limit;

    /**
     * @var array|string
     */
    protected $models;
    protected $offset;
    protected $order;
    protected $sharedLock;
    protected ?object $instance = null;

    /**
     * Phalcon\Mvc\Model\Query\Builder constructor
     */
    public function __construct(?array $params = null, ?DiInterface $container = null) {
        if (is_array($params)) {
            /**
             * Process conditions
             */
            $conditions = $params[0] ?? null;
            if ($conditions !== null) {
                $this->conditions = $conditions;
            } else {
                $conditions = $params["conditions"] ?? null;
                if ($conditions !== null) {
                    $this->conditions = $conditions;
                }
            }

            if (is_array($conditions)) {
                $mergedConditions = [];
                $mergedParams = [];
                $mergedTypes = [];

                foreach ($conditions as $singleConditionArray) {
                    if (is_array($singleConditionArray)) {
                        $singleCondition = $singleConditionArray[0];
                        $singleParams = $singleConditionArray[1];
                        $singleTypes = $singleConditionArray[2];

                        if (is_string($singleCondition)) {
                            $mergedConditions[] = $singleCondition;
                        }

                        if (is_array($singleParams)) {
                            $mergedParams = $mergedParams + $singleParams;
                        }

                        if (is_array($singleTypes)) {
                            $mergedTypes = $mergedTypes + $singleTypes;
                        }
                    }
                }

                $this->conditions = implode(" AND ", $mergedConditions);

                $this->bindParams = $mergedParams;
                $this->bindTypes = $mergedTypes;
            }

            /**
             * Assign bind types
             */
            $bind = $params["bind"] ?? null;
            if ($bind !== null) {
                $this->bindParams = $bind;
            }
            $bindTypes = $params["bindTypes"] ?? null;
            if ($bindTypes !== null) {
                $this->bindTypes = $bindTypes;
            }

            /**
             * Assign SELECT DISTINCT / SELECT ALL clause
             */
            $distinct = $params["distinct"] ?? null;
            if ($distinct !== null) {
                $this->distinct = $distinct;
            }

            /**
             * Assign FROM clause
             */
            $fromClause = $params["models"] ?? null;
            if ($fromClause !== null) {
                $this->models = $fromClause;
            }

            /**
             * Assign COLUMNS clause
             */
            $columns = $params["columns"] ?? null;
            if ($columns !== null) {
                $this->columns = $columns;
            }

            /**
             * Assign JOIN clause
             */
            $joinsClause = $params["joins"] ?? null;
            if ($joinsClause !== null) {
                $this->joins = $joinsClause;
            }

            /**
             * Assign GROUP clause
             */
            $groupClause = $params["group"] ?? null;
            if ($groupClause !== null) {
                $this->groupBy($groupClause);
            }

            /**
             * Assign HAVING clause
             */
            $havingClause = $params["having"] ?? null;
            if ($havingClause !== null) {
                $this->having = $havingClause;
            }

            /**
             * Assign ORDER clause
             */
            $orderClause = $params["order"] ?? null;
            if ($orderClause !== null) {
                $this->order = $orderClause;
            }

            /**
             * Assign LIMIT clause
             */
            $limitClause = $params["limit"] ?? null;
            if ($limitClause !== null) {
                if (is_array($limitClause)) {
                    if ($limit = $limitClause[0] ?? null) {
                        if (is_int($limit)) {
                            $this->limit = $limit;
                        }
                    }
                    if ($offset = $limitClause[1] ?? null) {
                        if (is_int($offset)) {
                            $this->offset = $offset;
                        }
                    }
                } else {
                    $this->limit = $limitClause;
                }
            } else {
                $this->limit = null;
            }


            /**
             * Assign OFFSET clause
             */
            $offsetClause = $params["offset"] ?? null;
            if ($offsetClause !== null) {
                $this->offset = $offsetClause;
            }

            /**
             * Assign FOR UPDATE clause
             */
            $forUpdate = $params["for_update"] ?? null;
            if ($forUpdate !== null) {
                $this->forUpdate = $forUpdate;
            }

            /**
             * Assign SHARED LOCK clause
             */
            $sharedLock = $params["shared_lock"] ?? null;
            if ($sharedLock !== null) {
                $this->sharedLock = $sharedLock;
            }
        } else {
            if (is_string($params) && strlen($params) > 0) {
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
     *
     * ```php
     * // Load data from models Robots
     * $builder->addFrom(
     *     Robots::class
     * );
     *
     * // Load data from model 'Robots' using 'r' as alias in PHQL
     * $builder->addFrom(
     *     Robots::class,
     *     "r"
     * );
     * ```
     */
    public function addFrom(string $model, string $alias = null): BuilderInterface {
        $models = $this->models;

        if (!is_array($models)) {
            if ($models !== null) {
                $currentModel = $models;
                $models = [$currentModel];
            } else {
                $models = [];
            }
        }

        if (is_string($alias) && $alias !== null) {
            $models[$alias] = $model;
        } else {
            $models[] = $model;
        }

        $this->models = $models;

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using a AND operator
     *
     * ```php
     * $builder->andHaving("SUM(Robots.price) > 0");
     *
     * $builder->andHaving(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     * ```
     */
    public function andHaving(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $currentConditions = $this->having;

        /**
         * Nest the condition to current ones or set as unique
         */
        if (!empty($currentConditions)) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }

        return $this->having($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a condition to the current WHERE conditions using a AND operator
     *
     * ```php
     * $builder->andWhere("name = 'Peter'");
     *
     * $builder->andWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     * ```
     */
    public function andWhere(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $currentConditions = $this->conditions;

        /**
         * Nest the condition to current ones or set as unique
         */
        if (!empty($currentConditions)) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Automatically escapes identifiers but only if they need to be escaped.
     */
    final public function autoescape(string $identifier): string {
        if ((strpos($identifier, "[") !== false) || (strpos($identifier, ".") !== false) || is_numeric($identifier)) {
            return $identifier;
        }

        return  $identifier;
    }

    /**
     * Appends a BETWEEN condition to the current HAVING conditions clause
     *
     * ```php
     * $builder->betweenHaving("SUM(Robots.price)", 100.25, 200.50);
     * ```
     */
    public function betweenHaving(string $expr, $minimum, $maximum, string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionBetween("Having", $operator, $expr, $minimum, $maximum);
    }

    /**
     * Appends a BETWEEN condition to the current WHERE conditions
     *
     * ```php
     * $builder->betweenWhere("price", 100.25, 200.50);
     * ```
     */
    public function betweenWhere(string $expr, $minimum, $maximum, string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionBetween("Where", $operator, $expr, $minimum, $maximum);
    }

    /**
     * Sets the columns to be queried
     *
     * ```php
     * $builder->columns("id, name");
     *
     * $builder->columns(
     *     [
     *         "id",
     *         "name",
     *     ]
     * );
     *
     * $builder->columns(
     *     [
     *         "name",
     *         "number" => "COUNT(*)",
     *     ]
     * );
     * ```
     */
    public function columns($columns): BuilderInterface {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets SELECT DISTINCT / SELECT ALL flag
     *
     * ```php
     * $builder->distinct("status");
     * $builder->distinct(null);
     * ```
     */
    public function distinct($distinct): BuilderInterface {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Sets a FOR UPDATE clause
     *
     * ```php
     * $builder->forUpdate(true);
     * ```
     */
    public function forUpdate(bool $forUpdate): BuilderInterface {
        $this->forUpdate = $forUpdate;

        return $this;
    }

    /**
     * Sets the models who makes part of the query
     *
     * ```php
     * $builder->from(
     *     Robots::class
     * );
     *
     * $builder->from(
     *     [
     *         Robots::class,
     *         RobotsParts::class,
     *     ]
     * );
     *
     * $builder->from(
     *     [S
     *         "r"  => Robots::class,
     *         "rp" => RobotsParts::class,
     *     ]
     * );
     * ```
     */
    public function from($models): BuilderInterface {
        $this->models = $models;

        return $this;
    }

    /**
     * Returns default bind params
     */
    public function getBindParams(): array {
        return $this->bindParams;
    }

    /**
     * Returns default bind types
     */
    public function getBindTypes(): array {
        return $this->bindTypes;
    }

    /**
     * Return the columns to be queried
     *
     * @return string|array
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI(): DiInterface {
        return $this->container;
    }

    /**
     * Returns SELECT DISTINCT / SELECT ALL flag
     */
    public function getDistinct(): bool {
        return $this->distinct;
    }

    /**
     * Return the models who makes part of the query
     *
     * @return string|array
     */
    public function getFrom() {
        return $this->models;
    }

    /**
     * Returns the GROUP BY clause
     */
    public function getGroupBy(): array {
        return $this->group;
    }

    /**
     * Return the current having clause
     */
    public function getHaving(): string {
        return $this->having;
    }

    /**
     * Return join parts of the query
     */
    public function getJoins(): array {
        return $this->joins;
    }

    /**
     * Returns the current LIMIT clause
     *
     * @return string|array
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * Returns the models involved in the query
     */
    public function getModels(): string|array|null {
        $models = $this->models;

        if (is_array($models) && count($models) === 1) {
            return Arr::first($models);
        }

        return $models;
    }

    /**
     * Returns the current OFFSET clause
     */
    public function getOffset(): int {
        return $this->offset;
    }

    /**
     * Returns the set ORDER BY clause
     *
     * @return string|array
     */
    public function getOrderBy() {
        return $this->order;
    }

    /**
     * Returns a PHQL statement built based on the builder parameters
     */
    final public function getPhql(): string {
        $container = $this->container;
        if (!is_object($container)) {
            $container = Di::getDefault();
            $this->container = $container;
        }

        $model_names = $this->models;
        if (empty($model_names)) {
            throw new Exception(
                            "At least one model is required to build the query"
            );
        }
        if (is_array($model_names)) {
            if (count($model_names) != 1) {
                throw new Exception(
                                "SqlBuilder only handles one model at a time"
                );
            }
            $model_name = $model_names[0];
        } else {
            $model_name = $model_names;
        }
        $model = Create::instance_params(
                        $model_name,
                        [
                            null,
                            $container
                        ]
        );
        $this->instance = $model;

        $conditions = $this->conditions;
        $metaData = $container->getShared("modelsMetadata");
        $columnMap = $metaData->getColumnMap($model);
        
        if (is_numeric($conditions)) {

            $noPrimary = true;
            $primaryKeys = $metaData->getPrimaryKeyAttributes($model);

            if (!empty($primaryKeys)) {
                $firstPrimaryKey = $primaryKeys[0] ?? null;
                if ($firstPrimaryKey !== null) {
                    /**
                     * The PHQL contains the renamed columns if available
                     */
                    if (\globals_get("orm.column_renaming")) {
                       
                    } else {
                        $columnMap = null;
                    }

                    if (is_array($columnMap)) {
                        if (!$attributeField = $columnMap[$firstPrimaryKey]) {
                            throw new Exception(
                                            "Column '" . $firstPrimaryKey . "' isn't part of the column map"
                            );
                        }
                    } else {
                        $attributeField = $firstPrimaryKey;
                    }

                    // check the type of the condition, if it's a string put single quotes around the value
                    if (is_string($conditions)) {
                        /*
                         * Example : if the developer writes findFirstBy('135'), Phalcon will generate where uuid = 135.
                         * But the column's type is text so Postgres needs to have single quotes such as ;
                         * where uuid = '135'.
                         */
                        $conditions = "'" . $conditions . "'";
                    }

                    $conditions = $this->autoescape($model) . "." . $this->autoescape($attributeField) . " = " . $conditions;
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
                    if (is_int($columnAlias)) {
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
                $phql .= "*";
        }
        
        $phql .= " FROM " . $this->autoescape($model->getSource());
 
        // Only append where conditions if it's string
        if (is_string($conditions)) {
            if (!empty($conditions)) {
                $phql .= " WHERE " . $conditions;
            }
        }

        /**
         * Process group parameters
         */
        $group = $this->group;
        if ($group !== null) {
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
            if (!empty($having)) {
                $phql .= " HAVING " . $having;
            }
        }

        /**
         * Process order clause
         */
        $order = $this->order;

        if ($order !== null) {
            if (is_array($order)) {
                $orderItems = [];

                foreach ($order as $orderItem) {
                    /**
                     * For case 'ORDER BY 1'
                     */
                    if (is_int($orderItem)) {
                        $orderItems[] = $orderItem;

                        continue;
                    }

                    if (strpos($orderItem, " ") !== false) {
                        $itemExplode = explode(" ", $orderItem);
                        $orderItems[] = $this->autoescape($itemExplode[0]) . " " . $itemExplode[1];

                        continue;
                    }

                    $orderItems[] = $this->autoescape($orderItem);
                }

                $phql .= " ORDER BY " . join(", ", $orderItems);
            } else {
                $phql .= " ORDER BY " . $order;
            }
        }

        /**
         * Process limit parameters
         */
        $limit = $this->limit;
        if ($limit !== null) {
            $number = null;

            if (is_array($limit)) {
                $number = $limit["number"];
                $offset = $limit["offset"] ?? null;
                if ($offset !== null) {
                    if (!is_numeric($offset)) {
                        $offset = 0;
                    }
                }
            } else {
                if (is_numeric($limit)) {
                    $number = $limit;
                    $offset = $this->offset;
                    if ($offset !== null) {
                        if (!is_numeric($offset)) {
                            $offset = 0;
                        }
                    }
                }
            }

            if (is_numeric($number)) {
                $phql .= " LIMIT :APL0";
                $this->bindParams[":APL0"] = intval($number, 10);
                $this->bindTypes[":APL0"] = Column::BIND_PARAM_INT;

                if (is_numeric($offset) && $offset !== 0) {
                    $phql .= " OFFSET :APL1";
                    $this->bindParams[":APL1"] = intval($offset, 10);
                    $this->bindTypes[":APL1"] = Column::BIND_PARAM_INT;
                }
            }
        }

        $forUpdate = $this->forUpdate;

        if (is_bool($forUpdate) && $forUpdate) {
            $phql .= " FOR UPDATE";
        }

        return $phql;
    }

    /**
     * Returns the query built
     */
    public function getQuery(): QueryInterface {
        $phql = $this->getPhql();

        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                            Exception::$containerServiceNotFound(
                                    "the services related to the ORM"
                            )
            );
        }

        /**
         * Gets Query instance from DI container
         */
        $query = $container->get(
                "Phalcon\\Mvc\\Model\\SqlQuery",
                [$phql, $container]
        );

        // Set default bind params
        $bindParams = $this->bindParams;
        if (is_array($bindParams)) {
            $query->setBindParams($bindParams);
        }

        // Set default bind types
        $bindTypes = $this->bindTypes;
        if (is_array($bindTypes)) {
            $query->setBindTypes($bindTypes);
        }

        if (is_bool($this->sharedLock)) {
            $query->setSharedLock($this->sharedLock);
        }

        return $query;
    }

    /**
     * Return the conditions for the query
     *
     * @return string|array
     */
    public function getWhere() {
        return $this->conditions;
    }

    /**
     * Sets a GROUP BY clause
     *
     * ```php
     * $builder->groupBy(
     *     [
     *         "Robots.name",
     *     ]
     * );
     * ```
     *
     * @param string|array group
     */
    public function groupBy($group): BuilderInterface {
        if (is_string($group)) {
            if (strpos($group, ",") !== false) {
                $group = str_replace(" ", "", $group);
            }

            $group = explode(",", $group);
        }

        $this->group = $group;

        return $this;
    }

    /**
     * Sets the HAVING condition clause
     *
     * ```php
     * $builder->having("SUM(Robots.price) > 0");
     *
     * $builder->having(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     * ```
     */
    public function having($conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $this->having = $conditions;

        $currentBindParams = $this->bindParams;

        /**
         * Merge the bind params to the current ones
         */
        if (is_array($currentBindParams)) {
            $this->bindParams = $currentBindParams + $bindParams;
        } else {
            $this->bindParams = $bindParams;
        }

        $currentBindTypes = $this->bindTypes;

        /**
         * Merge the bind types to the current ones
         */
        if (is_array($currentBindTypes)) {
            $this->bindTypes = $currentBindTypes + $bindTypes;
        } else {
            $this->bindTypes = $bindTypes;
        }

        return $this;
    }

    /**
     * Appends an IN condition to the current HAVING conditions clause
     *
     * ```php
     * $builder->inHaving("SUM(Robots.price)", [100, 200]);
     * ```
     */
    public function inHaving(string $expr, array $values, string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionIn("Having", $operator, $expr, $values);
    }

    /**
     * Adds an INNER join to the query
     *
     * ```php
     * // Inner Join model 'Robots' with automatic conditions and alias
     * $builder->innerJoin(
     *     Robots::class
     * );
     *
     * // Inner Join model 'Robots' specifying conditions
     * $builder->innerJoin(
     *     Robots::class,
     *     "Robots.id = RobotsParts.robots_id"
     * );
     *
     * // Inner Join model 'Robots' specifying conditions and alias
     * $builder->innerJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     * ```
     */
    public function innerJoin(string $model, string $conditions = null, string $alias = null): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "INNER"];

        return $this;
    }

    /**
     * Appends an IN condition to the current WHERE conditions
     *
     * ```php
     * $builder->inWhere(
     *     "id",
     *     [1, 2, 3]
     * );
     * ```
     */
    public function inWhere(string $expr, array $values, string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionIn("Where", $operator, $expr, $values);
    }

    /**
     * Adds an :type: join (by default type - INNER) to the query
     *
     * ```php
     * // Inner Join model 'Robots' with automatic conditions and alias
     * $builder->join(
     *     Robots::class
     * );
     *
     * // Inner Join model 'Robots' specifying conditions
     * $builder->join(
     *     Robots::class,
     *     "Robots.id = RobotsParts.robots_id"
     * );
     *
     * // Inner Join model 'Robots' specifying conditions and alias
     * $builder->join(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     *
     * // Left Join model 'Robots' specifying conditions, alias and type of join
     * $builder->join(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r",
     *     "LEFT"
     * );
     * ```
     */
    public function join(string $model, ?string $conditions = null,
            ?string $alias = null, ?string $type = null): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, $type];

        return $this;
    }

    /**
     * Adds a LEFT join to the query
     *
     * ```php
     * $builder->leftJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     * ```
     */
    public function leftJoin(string $model, string $conditions = null, string $alias = null): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "LEFT"];

        return $this;
    }

    /**
     * Sets a LIMIT clause, optionally an offset clause
     *
     * ```php
     * $builder->limit(100);
     * $builder->limit(100, 20);
     * $builder->limit("100", "20");
     * ```
     */
    public function limit(int $limit, null|int|string $offset = null): BuilderInterface {
        $limit = abs($limit);

        if ($limit === 0) {
            return $this;
        }

        $this->limit = $limit;

        if (is_numeric($offset)) {
            $this->offset = abs((int) $offset);
        }

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition to the current HAVING conditions clause
     *
     * ```php
     * $builder->notBetweenHaving("SUM(Robots.price)", 100.25, 200.50);
     * ```
     */
    public function notBetweenHaving(string $expr, $minimum, $maximum,
            string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
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
     *
     * ```php
     * $builder->notBetweenWhere("price", 100.25, 200.50);
     * ```
     */
    public function notBetweenWhere(string $expr, $minimum, $maximum,
            string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
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
     *
     * ```php
     * $builder->notInHaving("SUM(Robots.price)", [100, 200]);
     * ```
     */
    public function notInHaving(string $expr, array $values,
            string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionNotIn("Having", $operator, $expr, $values);
    }

    /**
     * Appends a NOT IN condition to the current WHERE conditions
     *
     * ```php
     * $builder->notInWhere("id", [1, 2, 3]);
     * ```
     */
    public function notInWhere(string $expr, array $values,
            string $operator = BuilderInterface::OPERATOR_AND): BuilderInterface {
        return $this->conditionNotIn("Where", $operator, $expr, $values);
    }

    /**
     * Sets an OFFSET clause
     *
     * ```php
     * $builder->offset(30);
     * ```
     */
    public function offset(int $offset): BuilderInterface {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Sets an ORDER BY condition clause
     *
     * ```php
     * $builder->orderBy("Robots.name");
     * $builder->orderBy(["1", "Robots.name"]);
     * $builder->orderBy(["Robots.name DESC"]);
     * ```
     *
     * @param string|array orderBy
     */
    public function orderBy($orderBy): BuilderInterface {
        $this->order = $orderBy;

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using an OR operator
     *
     * ```php
     * $builder->orHaving("SUM(Robots.price) > 0");
     *
     * $builder->orHaving(
     *     "SUM(Robots.price) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     * ```
     */
    public function orHaving(string $conditions,
            array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $currentConditions = $this->having;

        /**
         * Nest the condition to current ones or set as unique
         */
        if (!empty($currentConditions)) {
            $conditions = "(" . $currentConditions . ") OR (" . $conditions . ")";
        }

        return $this->having($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a condition to the current conditions using an OR operator
     *
     * ```php
     * $builder->orWhere("name = 'Peter'");
     *
     * $builder->orWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     * ```
     */
    public function orWhere(string $conditions,
            array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $currentConditions = $this->conditions;

        /**
         * Nest the condition to current ones or set as unique
         */
        if (!empty($currentConditions)) {
            $conditions = "(" . $currentConditions . ") OR (" . $conditions . ")";
        }

        return $this->where($conditions, $bindParams, $bindTypes);
    }

    /**
     * Adds a RIGHT join to the query
     *
     * ```php
     * $builder->rightJoin(
     *     Robots::class,
     *     "r.id = RobotsParts.robots_id",
     *     "r"
     * );
     * ```
     */
    public function rightJoin(string $model,
            string $conditions = null, string $alias = null): BuilderInterface {
        $this->joins[] = [$model, $conditions, $alias, "RIGHT"];

        return $this;
    }

    /**
     * Set default bind parameters
     */
    public function setBindParams(array $bindParams, bool $merge = false): BuilderInterface {
        if ($merge) {
            $currentBindParams = $this->bindParams;
            if (is_array($currentBindParams)) {
                $this->bindParams = $currentBindParams + $bindParams;
            } else {
                $this->bindParams = $bindParams;
            }
        } else {
            $this->bindParams = $bindParams;
        }

        return $this;
    }

    /**
     * Set default bind types
     */
    public function setBindTypes(array $bindTypes, bool $merge = false): BuilderInterface {
        if ($merge) {
            $currentBindTypes = $this->bindTypes;

            if (is_array($currentBindTypes)) {
                $this->bindTypes = $currentBindTypes + $bindTypes;
            } else {
                $this->bindTypes = $bindTypes;
            }
        } else {
            $this->bindTypes = $bindTypes;
        }

        return $this;
    }

    /**
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $container): void {
        $this->container = $container;
    }

    /**
     * Sets the query WHERE conditions
     *
     * ```php
     * $builder->where(100);
     *
     * $builder->where("name = 'Peter'");
     *
     * $builder->where(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     * ```
     */
    public function where(string $conditions, array $bindParams = [], array $bindTypes = []): BuilderInterface {
        $this->conditions = $conditions;

        /**
         * Merge the bind params to the current ones
         */
        if (count($bindParams) > 0) {
            $currentBindParams = $this->bindParams;

            if (is_array($currentBindParams)) {
                $this->bindParams = $currentBindParams + $bindParams;
            } else {
                $this->bindParams = $bindParams;
            }
        }

        /**
         * Merge the bind types to the current ones
         */
        if (count($bindTypes) > 0) {
            $currentBindTypes = $this->bindTypes;

            if (is_array($currentBindTypes)) {
                $this->bindTypes = $currentBindTypes + $bindTypes;
            } else {
                $this->bindTypes = $bindTypes;
            }
        }

        return $this;
    }

    /**
     * Appends a BETWEEN condition
     */
    protected function conditionBetween(string $clause, string $operator, string $expr, $minimum, $maximum): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new Exception(
                            sprintf(
                                    "Operator % is not available.",
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
        $this->{$operatorMethod}(
                $expr . " BETWEEN :" . $minimumKey . ": AND :" . $maximumKey . ":",
                [
                    $minimumKey => $minimum,
                    $maximumKey => $maximum
                ]
        );

        $this->hiddenParamNumber = $nextHiddenParam + 1;

        return $this;
    }

    /**
     * Appends an IN condition
     */
    protected function conditionIn(string $clause, string $operator, string $expr, array $values): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new Exception(
                            sprintf(
                                    "Operator % is not available.",
                                    $operator
                            )
            );
        }

        $operatorMethod = $operator . $clause;

        if (!count($values)) {
            $this->{$operatorMethod}($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = (int) $this->hiddenParamNumber;

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
        $this->{$operatorMethod}(
                $expr . " IN (" . join(", ", $bindKeys) . ")",
                $bindParams
        );

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition
     */
    protected function conditionNotBetween(string $clause, string $operator,
            string $expr, $minimum, $maximum): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new Exception(
                            sprintf(
                                    "Operator % is not available.",
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
        $this->{$operatorMethod}(
                $expr . " NOT BETWEEN :" . $minimumKey . ": AND :" . $maximumKey . ":",
                [
                    $minimumKey => $minimum,
                    $maximumKey => $maximum
                ]
        );
        $this->hiddenParamNumber = $nextHiddenParam + 1;

        return $this;
    }

    /**
     * Appends a NOT IN condition
     */
    protected function conditionNotIn(string $clause, string $operator, string $expr, array $values): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new Exception(
                            sprintf(
                                    "Operator % is not available.",
                                    $operator
                            )
            );
        }

        $operatorMethod = $operator . $clause;

        if (!count($values)) {
            $this->{$operatorMethod}($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = (int) $this->hiddenParamNumber;

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
        $this->{$operatorMethod}($expr . " NOT IN (" . join(", ", $bindKeys) . ")", $bindParams);

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }

}
