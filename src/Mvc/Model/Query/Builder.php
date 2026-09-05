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
use Phalcon\Db\Column;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Exceptions\ManagerOrmServicesUnavailable;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Query\Exceptions\Builder\BuilderColumnNotInMap;
use Phalcon\Mvc\Model\Query\Exceptions\Builder\BuilderConditionInvalid;
use Phalcon\Mvc\Model\Query\Exceptions\Builder\ModelRequired;
use Phalcon\Mvc\Model\Query\Exceptions\Builder\NoPrimaryKey;
use Phalcon\Mvc\Model\Query\Exceptions\Builder\OperatorNotAvailable;
use Phalcon\Mvc\Model\QueryInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;

use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function is_object;
use function is_string;
use function str_contains;

/**
 * Helps to create PHQL queries using an OO interface
 *
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
 *
 * $queryBuilder = new \Phalcon\Mvc\Model\Query\Builder($params);
 *```
 *
 * @phpstan-import-type mvc_model_bind_params from MvcTypes
 * @phpstan-import-type mvc_model_bind_types from MvcTypes
 * @phpstan-import-type mvc_query_builder_join from MvcTypes
 * @phpstan-import-type mvc_query_builder_params from MvcTypes
 * @phpstan-import-type mvc_query_columns from MvcTypes
 * @phpstan-import-type mvc_query_order from MvcTypes
 */
class Builder implements BuilderInterface, InjectionAwareInterface
{
    /**
     * @phpstan-var mvc_model_bind_params
     */
    protected array $bindParams = [];

    /**
     * @phpstan-var mvc_model_bind_types
     */
    protected array $bindTypes = [];

    /**
     * @phpstan-var mvc_query_columns|null
     */
    protected array | string | null $columns = null;

    /**
     * @phpstan-var array<array-key, mixed>|int|string|null
     */
    protected array | int | string | null $conditions = null;

    /**
     * @phpstan-var DiInterface|null
     */
    protected object | null $container;

    protected mixed $distinct = null;

    protected bool $forUpdate = false;

    /**
     * @var array|null
     *
     * @phpstan-var array<array-key, string>|null
     */
    protected $group = [];

    protected string | null $having = null;

    protected int $hiddenParamNumber = 0;

    /**
     * @phpstan-var array<array-key, mvc_query_builder_join>
     */
    protected array $joins = [];

    /**
     * @phpstan-var array<array-key, mixed>|int|string|null
     */
    protected array | int | string | null $limit = null;

    /**
     * @phpstan-var mvc_query_columns|null
     */
    protected array | string | null $models = null;

    protected int $offset = 0;

    /**
     * @phpstan-var array<array-key, int|string>|string|null
     */
    protected array | string | null $order = null;

    protected string $resultsetRowClass = "";

    protected bool $sharedLock = false;

    /**
     * Phalcon\Mvc\Model\Query\Builder constructor
     */
    public function __construct(
        mixed $params = null,
        DiInterface | null $container = null
    ) {
        if (is_array($params)) {
            /**
             * The keys of the array hold the clauses of the query.
             *
             * @var mvc_query_builder_params $params
             */

            /**
             * Process conditions
             */
            $conditions = '';
            if (isset($params[0])) {
                $conditions       = $params[0];
                $this->conditions = $conditions;
            } else {
                if (isset($params["conditions"])) {
                    $conditions       = $params["conditions"];
                    $this->conditions = $conditions;
                }
            }

            if (is_array($conditions)) {
                $mergedConditions = [];
                $mergedParams     = [];
                $mergedTypes      = [];

                foreach ($conditions as $singleConditionArray) {
                    if (is_array($singleConditionArray)) {
                        $singleCondition = $singleConditionArray[0] ?? null;
                        /**
                         * A condition entry is a triple. The second element
                         * holds its bind values, the third their bind types.
                         *
                         * @var mvc_model_bind_params|null $singleParams
                         */
                        $singleParams    = $singleConditionArray[1] ?? null;
                        /** @var mvc_model_bind_types|null $singleTypes */
                        $singleTypes     = $singleConditionArray[2] ?? null;

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
                $this->bindTypes  = $mergedTypes;
            }

            /**
             * Assign bind types
             */
            if (isset($params["bind"])) {
                $this->bindParams = $params["bind"];
            }

            if (isset($params["bindTypes"])) {
                $this->bindTypes = $params["bindTypes"];
            }

            /**
             * Assign SELECT DISTINCT / SELECT ALL clause
             */
            if (isset($params["distinct"])) {
                $this->distinct = $params["distinct"];
            }

            /**
             * Assign FROM clause
             */
            if (isset($params["models"])) {
                $this->models = $params["models"];
            }

            /**
             * Assign COLUMNS clause
             */
            if (isset($params["columns"])) {
                $this->columns = $params["columns"];
            }

            /**
             * Assign JOIN clause
             */
            if (isset($params["joins"])) {
                $this->joins = $params["joins"];
            }

            /**
             * Assign GROUP clause
             */
            if (isset($params["group"])) {
                $this->groupBy($params["group"]);
            }

            /**
             * Assign HAVING clause
             */
            if (isset($params["having"])) {
                $this->having = $params["having"];
            }

            /**
             * Assign ORDER clause
             */
            if (isset($params["order"])) {
                $this->order = $params["order"];
            }

            /**
             * Assign LIMIT clause
             */
            if (isset($params["limit"])) {
                $limitClause = $params["limit"];
                if (is_array($limitClause)) {
                    if (isset($limitClause[0])) {
                        if (is_int($limitClause[0])) {
                            $this->limit = $limitClause[0];
                        }

                        if (isset($limitClause[1]) && is_int($limitClause[1])) {
                            $this->offset = $limitClause[1];
                        }
                    } else {
                        $this->limit = $limitClause;
                    }
                } else {
                    $this->limit = $limitClause;
                }
            }

            /**
             * Assign OFFSET clause
             */
            if (isset($params["offset"])) {
                $this->offset = $params["offset"];
            }

            /**
             * Assign FOR UPDATE clause
             */
            if (isset($params["for_update"])) {
                $this->forUpdate = $params["for_update"];
            }

            /**
             * Assign SHARED LOCK clause
             */
            if (isset($params["shared_lock"])) {
                $this->sharedLock = $params["shared_lock"];
            }
        } else {
            if (is_string($params) && $params !== "") {
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
     *```php
     * // Load data from models Invoices
     * $builder->addFrom(
     *     Invoices::class
     * );
     *
     * // Load data from model 'Invoices' using 'r' as alias in PHQL
     * $builder->addFrom(
     *     Invoices::class,
     *     "r"
     * );
     *```
     */
    public function addFrom(string $model, string | null $alias = null): BuilderInterface
    {
        if (!is_array($this->models)) {
            if ($this->models !== null) {
                $this->models = [$this->models];
            } else {
                $this->models = [];
            }
        }

        if (is_string($alias) && $alias !== null) {
            $this->models[$alias] = $model;
        } else {
            $this->models[] = $model;
        }

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using a AND operator
     *
     *```php
     * $builder->andHaving("SUM(Invoices.inv_total) > 0");
     *
     * $builder->andHaving(
     *     "SUM(Invoices.inv_total) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function andHaving(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
        /**
         * Nest the condition to current ones or set as unique
         */
        if ($this->having) {
            $conditions = "(" . $this->having . ") AND (" . $conditions . ")";
        }

        return $this->having($conditions, $bindParams, $bindTypes);
    }

    /**
     * Appends a condition to the current WHERE conditions using a AND operator
     *
     *```php
     * $builder->andWhere("name = 'Peter'");
     *
     * $builder->andWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     *```
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function andWhere(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
        /**
         * The constructor replaces an array of conditions with the imploded
         * string, so only a scalar reaches this point.
         *
         * @var int|string|null $currentConditions
         */
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
        if (
            str_contains($identifier, "[") ||
            str_contains($identifier, ".") ||
            str_contains($identifier, "(") ||
            is_numeric($identifier)
        ) {
            return $identifier;
        }

        return "[" . $identifier . "]";
    }

    /**
     * Appends a BETWEEN condition to the current HAVING conditions clause
     *
     *```php
     * $builder->betweenHaving("SUM(Invoices.inv_total)", 100.25, 200.50);
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
     *
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
     *
     * When using an array as a parameter, you will need to specify one field
     * per array element. If a non-numeric key is defined in the array, it will
     * be used as the alias in the query
     *
     *```php
     * <?php
     *
     * // String, comma separated values
     * $builder->columns("id, category");
     *
     * // Array, one column per element
     * $builder->columns(
     *     [
     *         "inv_id",
     *         "inv_total",
     *     ]
     * );
     *
     * // Array with named key. The name of the key acts as an
     * // alias (`AS` clause)
     * $builder->columns(
     *     [
     *         "inv_cst_id",
     *         "total_invoices" => "COUNT(*)",
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
     */
    public function columns(mixed $columns): BuilderInterface
    {
        /**
         * The caller passes one column or a list of columns.
         *
         * @var mvc_query_columns|null $columns
         */
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets SELECT DISTINCT / SELECT ALL flag
     *
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
     *
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
     *
     *```php
     * $builder->from(
     *     Invoices::class
     * );
     *
     * $builder->from(
     *     [
     *         Invoices::class,
     *         OrdersProducts::class,
     *     ]
     * );
     *
     * $builder->from(
     *     [
     *         "r"  => Invoices::class,
     *         "rp" => OrdersProducts::class,
     *     ]
     * );
     *```
     *
     * @phpstan-param mvc_query_columns $models
     */
    public function from(mixed $models): BuilderInterface
    {
        $this->models = $models;

        return $this;
    }

    /**
     * Returns default bind params
     *
     * @phpstan-return mvc_model_bind_params
     */
    public function getBindParams(): array
    {
        return $this->bindParams;
    }

    /**
     * Returns default bind types
     *
     * @phpstan-return mvc_model_bind_types
     */
    public function getBindTypes(): array
    {
        return $this->bindTypes;
    }

    /**
     * Return the columns to be queried
     *
     * @return array|string
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI(): DiInterface
    {
        /**
         * The builder is always constructed with a container.
         */
        /** @var DiInterface $container */
        $container = $this->container;

        return $container;
    }

    /**
     * Returns SELECT DISTINCT / SELECT ALL flag
     */
    public function getDistinct(): bool
    {
        /** @var bool $distinct */
        $distinct = $this->distinct;

        return $distinct;
    }

    /**
     * Return the models who makes part of the query
     *
     * @return array|string|null
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getFrom()
    {
        return $this->models;
    }

    /**
     * Returns the GROUP BY clause
     *
     * @phpstan-return array<array-key, string>
     */
    public function getGroupBy(): array
    {
        /**
         * groupBy() stores the clause before any read.
         */
        /** @var array<array-key, string> $group */
        $group = $this->group;

        return $group;
    }

    /**
     * Return the current having clause
     */
    public function getHaving(): string | null
    {
        return $this->having;
    }

    /**
     * Return join parts of the query
     *
     * @phpstan-return array<array-key, mvc_query_builder_join>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * Returns the current LIMIT clause
     *
     * @return array|string
     *
     * @phpstan-return array<array-key, mixed>|int|string|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Returns the models involved in the query
     *
     * @phpstan-return mvc_query_columns|null
     */
    public function getModels(): array | string | null
    {
        if (is_array($this->models) && count($this->models) == 1) {
            return reset($this->models);
        }

        return $this->models;
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
     *
     * @phpstan-return array<array-key, int|string>|string|null
     */
    public function getOrderBy()
    {
        return $this->order;
    }

    /**
     * Returns a PHQL statement built based on the builder parameters
     *
     * @throws Exception
     */
    final public function getPhql(): string
    {
        if (!is_object($this->container)) {
            $this->container = Di::getDefault();
        }

        $models = $this->models;
        if (is_array($models)) {
            if (empty($models)) {
                throw new ModelRequired();
            }
        } else {
            if (!$models) {
                throw new ModelRequired();
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
                    throw new BuilderConditionInvalid();
                }

                $model = $models[0];
            } else {
                $model = $models;
            }

            /**
             * Get the models metadata service to obtain the column names,
             * column map and primary key
             */
            if ($this->container instanceof DiInterface) {
                /** @var MetaDataInterface $metaData */
                $metaData = $this->container->getShared("modelsMetadata");
            } else {
                /** @var MetaDataInterface $metaData */
                $metaData = $this->container->get("modelsMetadata");
            }
            /** @var ModelInterface $modelInstance */
            $modelInstance = new $model(null, $this->container);
            $noPrimary     = true;
            $primaryKeys   = $metaData->getPrimaryKeyAttributes($modelInstance);

            if (!empty($primaryKeys) && isset($primaryKeys[0])) {
                $firstPrimaryKey = $primaryKeys[0];
                /**
                 * The PHQL contains the renamed columns if available
                 */
                if (Settings::get("orm.column_renaming")) {
                    $columnMap = $metaData->getColumnMap($modelInstance);
                } else {
                    $columnMap = null;
                }

                if (is_array($columnMap)) {
                    if (!isset($columnMap[$firstPrimaryKey])) {
                        throw new BuilderColumnNotInMap($firstPrimaryKey);
                    } else {
                        $attributeField = $columnMap[$firstPrimaryKey];
                    }
                } else {
                    $attributeField = $firstPrimaryKey;
                }

                /**
                 * Use a named bind parameter instead of embedding the value
                 * directly in the PHQL string. Embedding produces a unique
                 * PHQL string per ID value, causing unbounded growth of the
                 * internal PHQL cache in long-running processes.
                 */
                $this->bindParams["APK0"] = $conditions;
                $conditions               = $this->autoescape($model)
                    . "."
                    . $this->autoescape($attributeField)
                    . " = :APK0:";
                $noPrimary                = false;
            }

            /**
             * A primary key is mandatory in these cases
             */
            if ($noPrimary) {
                throw new NoPrimaryKey();
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
                        $selectedColumns[] = $column
                            . " AS "
                            . $this->autoescape($columnAlias);
                    }
                }

                $phql .= implode(", ", $selectedColumns);
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
                    if (is_int($modelColumnAlias)) {
                        $selectedColumn = $this->autoescape($model) . ".*";
                    } else {
                        $selectedColumn = $this->autoescape($modelColumnAlias) . ".*";
                    }

                    $selectedColumns[] = $selectedColumn;
                }

                $phql .= implode(", ", $selectedColumns);
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
                    $selectedModel = $this->autoescape($model)
                        . " AS "
                        . $this->autoescape($modelAlias);
                } else {
                    $selectedModel = $this->autoescape($model);
                }

                $selectedModels[] = $selectedModel;
            }

            $phql .= " FROM " . implode(", ", $selectedModels);
        } else {
            $phql .= " FROM " . $this->autoescape($models);
        }

        /**
         * Check if joins were passed to the builders
         */
        $joins = $this->joins;

        if (is_array($joins)) {
            foreach ($joins as $join) {
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
        }

        // Only append where conditions if it's string
        if (is_string($conditions) && !empty($conditions)) {
            $phql .= " WHERE " . $conditions;
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

            $phql .= " GROUP BY " . implode(", ", $groupItems);
        }

        /**
         * Process having clause
         */
        $having = $this->having;
        if ($having !== null && !empty($having)) {
            $phql .= " HAVING " . $having;
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

                    /**
                     * For cases 'ORDER BY column ASC' and complex expressions
                     */
                    $itemTrimmed       = trim($orderItem);
                    $lastSpacePosition = strrpos($itemTrimmed, " ");

                    if (false !== $lastSpacePosition) {
                        $perhapsExpression = trim(substr($itemTrimmed, 0, $lastSpacePosition));
                        $perhapsDirection  = rtrim(substr($itemTrimmed, $lastSpacePosition + 1));

                        if (
                            strcasecmp($perhapsDirection, "desc") === 0 ||
                            strcasecmp($perhapsDirection, "asc") === 0
                        ) {
                            if (!str_contains($perhapsExpression, " ")) {
                                $perhapsExpression = $this->autoescape($perhapsExpression);
                            }

                            $orderItems[] = $perhapsExpression . " " . $perhapsDirection;
                        } else {
                            $orderItems[] = $itemTrimmed;
                        }

                        continue;
                    }

                    $orderItems[] = $this->autoescape($itemTrimmed);
                }

                $phql .= " ORDER BY " . implode(", ", $orderItems);
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
            $offset = null;

            if (is_array($limit)) {
                $number = $limit["number"];

                if (isset($limit["offset"])) {
                    $offset = $limit["offset"];
                    if (!is_numeric($offset)) {
                        $offset = 0;
                    }
                }
            } else {
                if (is_numeric($limit)) {
                    $number = $limit;
                    $offset = $this->offset;
                    if ($offset !== null && !is_numeric($offset)) {
                        $offset = 0;
                    }
                }
            }

            if (is_numeric($number)) {
                $phql                     .= " LIMIT :APL0:";
                $this->bindParams["APL0"] = intval($number, 10);
                $this->bindTypes["APL0"]  = Column::BIND_PARAM_INT;

                if (is_numeric($offset) && $offset !== 0) {
                    $phql                     .= " OFFSET :APL1:";
                    $this->bindParams["APL1"] = intval($offset, 10);
                    $this->bindTypes["APL1"]  = Column::BIND_PARAM_INT;
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
    public function getQuery(): QueryInterface
    {
        $phql      = $this->getPhql();
        $container = $this->container;

        if (!is_object($container)) {
            throw new ManagerOrmServicesUnavailable();
        }

        /**
         * Gets Query instance from DI container
         */
        /** @var QueryInterface $query */
        $query = $container->get(
            "Phalcon\\Mvc\\Model\\Query",
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

        /**
         * The accessor is not part of QueryInterface (see the interface's
         * v7 note), so a custom query service may not implement it.
         *
         * @todo v7: remove the method_exists() guard once the accessors are
         *       promoted to QueryInterface.
         */
        if (
            $this->resultsetRowClass !== ""
            && method_exists($query, "setResultsetRowClass")
        ) {
            $query->setResultsetRowClass($this->resultsetRowClass);
        }

        return $query;
    }

    /**
     * Returns the class that will be used to hydrate rows that are not mapped
     * to a model (custom columns/joins). An empty string means the default
     * Phalcon\Mvc\Model\Row is used.
     */
    public function getResultsetRowClass(): string
    {
        return $this->resultsetRowClass;
    }

    /**
     * Return the conditions for the query
     *
     * @return array|string|null
     *
     * @phpstan-return array<array-key, mixed>|string|null
     */
    public function getWhere()
    {
        /**
         * The setters store an array or a string only.
         *
         * @var array<array-key, mixed>|string|null $conditions
         */
        $conditions = $this->conditions;

        return $conditions;
    }

    /**
     * Sets a GROUP BY clause
     *
     *```php
     * $builder->groupBy(
     *     [
     *         "Invoices.inv_title",
     *     ]
     * );
     *```
     *
     * Passing null (or an empty array) clears the clause; the PHQL generator
     * treats both as "no GROUP BY".
     *
     * @param array|string|null $group
     *
     * @phpstan-param array<array-key, string>|string|null $group
     */
    public function groupBy(mixed $group): BuilderInterface
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
     *
     *```php
     * $builder->having("SUM(Invoices.inv_total) > 0");
     *
     * $builder->having(
     *     "SUM(Invoices.inv_total) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function having(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
        $this->having      = $conditions;
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
     *```php
     * $builder->inHaving("SUM(Invoices.inv_total)", [100, 200]);
     *```
     *
     * @phpstan-param array<array-key, mixed> $values
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
     *
     *```php
     * // Inner Join model 'Invoices' with automatic conditions and alias
     * $builder->innerJoin(
     *     Invoices::class
     * );
     *
     * // Inner Join model 'Invoices' specifying conditions
     * $builder->innerJoin(
     *     Invoices::class,
     *     "Invoices.inv_id = OrdersProducts.oxp_ord_id"
     * );
     *
     * // Inner Join model 'Invoices' specifying conditions and alias
     * $builder->innerJoin(
     *     Invoices::class,
     *     "r.inv_id = OrdersProducts.oxp_ord_id",
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
     *
     *```php
     * $builder->inWhere(
     *     "id",
     *     [1, 2, 3]
     * );
     *```
     *
     * @phpstan-param array<array-key, mixed> $values
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
     *
     *```php
     * // Inner Join model 'Invoices' with automatic conditions and alias
     * $builder->join(
     *     Invoices::class
     * );
     *
     * // Inner Join model 'Invoices' specifying conditions
     * $builder->join(
     *     Invoices::class,
     *     "Invoices.inv_id = OrdersProducts.oxp_ord_id"
     * );
     *
     * // Inner Join model 'Invoices' specifying conditions and alias
     * $builder->join(
     *     Invoices::class,
     *     "r.inv_id = OrdersProducts.oxp_ord_id",
     *     "r"
     * );
     *
     * // Left Join model 'Invoices' specifying conditions, alias and type of join
     * $builder->join(
     *     Invoices::class,
     *     "r.inv_id = OrdersProducts.oxp_ord_id",
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
     *
     *```php
     * $builder->leftJoin(
     *     Invoices::class,
     *     "r.inv_id = OrdersProducts.oxp_ord_id",
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
     *
     * ```php
     * $builder->limit(100);
     * $builder->limit(100, 20);
     * $builder->limit("100", "20");
     * ```
     */
    public function limit(int $limit, mixed $offset = null): BuilderInterface
    {
        $limit = abs($limit);

        if ($limit == 0) {
            return $this;
        }

        $this->limit = $limit;

        if (is_numeric($offset)) {
            $this->offset = abs((int)$offset);
        }

        return $this;
    }

    /**
     * Appends a NOT BETWEEN condition to the current HAVING conditions clause
     *
     *```php
     * $builder->notBetweenHaving("SUM(Invoices.inv_total)", 100.25, 200.50);
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
     *
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
     *
     *```php
     * $builder->notInHaving("SUM(Invoices.inv_total)", [100, 200]);
     *```
     *
     * @phpstan-param array<array-key, mixed> $values
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
     *
     *```php
     * $builder->notInWhere("id", [1, 2, 3]);
     *```
     *
     * @phpstan-param array<array-key, mixed> $values
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
     *
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
     *
     *```php
     * $builder->orderBy("Invoices.inv_title");
     * $builder->orderBy(["1", "Invoices.inv_title"]);
     * $builder->orderBy(["Invoices.inv_title DESC"]);
     *```
     */
    public function orderBy(mixed $orderBy): BuilderInterface
    {
        /**
         * The caller passes one order clause or a list of order clauses.
         *
         * @var array<array-key, int|string>|string|null $orderBy
         */
        $this->order = $orderBy;

        return $this;
    }

    /**
     * Appends a condition to the current HAVING conditions clause using an OR operator
     *
     *```php
     * $builder->orHaving("SUM(Invoices.inv_total) > 0");
     *
     * $builder->orHaving(
     *     "SUM(Invoices.inv_total) > :sum:",
     *     [
     *         "sum" => 100,
     *     ]
     * );
     *```
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function orHaving(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
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
     *
     *```php
     * $builder->orWhere("name = 'Peter'");
     *
     * $builder->orWhere(
     *     "name = :name: AND id > :id:",
     *     [
     *         "name" => "Peter",
     *         "id"   => 100,
     *     ]
     * );
     *```
     */
    public function orWhere(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
        /**
         * The constructor replaces an array of conditions with the imploded
         * string, so only a scalar reaches this point.
         *
         * @var int|string|null $currentConditions
         */
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
     *
     *```php
     * $builder->rightJoin(
     *     Invoices::class,
     *     "r.inv_id = OrdersProducts.oxp_ord_id",
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
     *
     * @phpstan-param mvc_model_bind_params $bindParams
     */
    public function setBindParams(array $bindParams, bool $merge = false): BuilderInterface
    {
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
     *
     * @phpstan-param mvc_model_bind_types $bindTypes
     */
    public function setBindTypes(array $bindTypes, bool $merge = false): BuilderInterface
    {
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
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Sets the class used to hydrate rows that are not mapped to a model
     * (custom columns/joins). The class must be a subclass of
     * Phalcon\Mvc\Model\Row. Validation is performed by the underlying
     * Phalcon\Mvc\Model\Query when the query is built.
     */
    public function setResultsetRowClass(string $resultsetRowClass): BuilderInterface
    {
        $this->resultsetRowClass = $resultsetRowClass;

        return $this;
    }

    /**
     * Sets the query WHERE conditions
     *
     *```php
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
     *```
     */
    public function where(
        string $conditions,
        array $bindParams = [],
        array $bindTypes = []
    ): BuilderInterface {
        $this->conditions = $conditions;

        /**
         * Merge the bind params to the current ones
         */
        if (!empty($bindParams)) {
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
        if (!empty($bindTypes)) {
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
    protected function conditionBetween(
        string $clause,
        string $operator,
        string $expr,
        mixed $minimum,
        mixed $maximum
    ): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new OperatorNotAvailable($operator);
        }

        $operatorMethod = $operator . $clause;

        $hiddenParam     = $this->hiddenParamNumber;
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
     *
     * @phpstan-param array<array-key, mixed> $values
     */
    protected function conditionIn(
        string $clause,
        string $operator,
        string $expr,
        array $values
    ): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new OperatorNotAvailable($operator);
        }

        $operatorMethod = $operator . $clause;

        if (empty($values)) {
            $this->$operatorMethod($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = (int)$this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys   = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key              = "AP" . $hiddenParam;
            $queryKey         = ":" . $key . ":";
            $bindKeys[]       = $queryKey;
            $bindParams[$key] = $value;
            $hiddenParam++;
        }

        /**
         * Create a standard IN condition with bind params
         * Append the IN to the current conditions using and "and"
         */
        $this->$operatorMethod(
            $expr . " IN (" . implode(", ", $bindKeys) . ")",
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
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new OperatorNotAvailable($operator);
        }

        $operatorMethod = $operator . $clause;

        $hiddenParam     = $this->hiddenParamNumber;
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
     *
     * @phpstan-param array<array-key, mixed> $values
     */
    protected function conditionNotIn(
        string $clause,
        string $operator,
        string $expr,
        array $values
    ): BuilderInterface {
        if ($operator !== Builder::OPERATOR_AND && $operator !== Builder::OPERATOR_OR) {
            throw new OperatorNotAvailable($operator);
        }

        $operatorMethod = $operator . $clause;

        if (empty($values)) {
            $this->$operatorMethod($expr . " != " . $expr);

            return $this;
        }

        $hiddenParam = (int)$this->hiddenParamNumber;

        $bindParams = [];
        $bindKeys   = [];

        foreach ($values as $value) {
            /**
             * Key with auto bind-params
             */
            $key              = "AP" . $hiddenParam;
            $queryKey         = ":" . $key . ":";
            $bindKeys[]       = $queryKey;
            $bindParams[$key] = $value;
            $hiddenParam++;
        }

        /**
         * Create a standard NOT IN condition with bind params
         * Append the NOT IN to the current conditions using and "and"
         */
        $this->$operatorMethod(
            $expr . " NOT IN (" . implode(", ", $bindKeys) . ")",
            $bindParams
        );

        $this->hiddenParamNumber = $hiddenParam;

        return $this;
    }
}
