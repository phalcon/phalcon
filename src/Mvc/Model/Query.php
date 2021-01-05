<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\Model;

use Phalcon\Db\Column;
use Phalcon\Db\RawValue;
use Phalcon\Db\ResultInterface;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Helper\Arr;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\Query\Status;
use Phalcon\Mvc\Model\Resultset\Complex;
use Phalcon\Mvc\Model\Query\StatusInterface;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Db\DialectInterface;
use Phalcon\Mvc\Model\Query\Lang;
use Phalcon\Reflect\Create;

/**
 * Phalcon\Mvc\Model\Query
 *
 * This class takes a PHQL intermediate representation and executes it.
 *
 * ```php
 * $phql = "SELECT c.price*0.16 AS taxes, c.* FROM Cars AS c JOIN Brands AS b
 *          WHERE b.name = :name: ORDER BY c.name";
 *
 * $result = $manager->executeQuery(
 *     $phql,
 *     [
 *         "name" => "Lamborghini",
 *     ]
 * );
 *
 * foreach ($result as $row) {
 *     echo "Name: ",  $row->cars->name, "\n";
 *     echo "Price: ", $row->cars->price, "\n";
 *     echo "Taxes: ", $row->taxes, "\n";
 * }
 *
 * // with transaction
 * use Phalcon\Mvc\Model\Query;
 * use Phalcon\Mvc\Model\Transaction;
 *
 * // $di needs to have the service "db" registered for this to work
 * $di = Phalcon\Di\FactoryDefault::getDefault();
 *
 * $phql = 'SELECT * FROM robot';
 *
 * $myTransaction = new Transaction($di);
 * $myTransaction->begin();
 *
 * $newRobot = new Robot();
 * $newRobot->setTransaction($myTransaction);
 * $newRobot->type = "mechanical";
 * $newRobot->name = "Astro Boy";
 * $newRobot->year = 1952;
 * $newRobot->save();
 *
 * $queryWithTransaction = new Query($phql, $di);
 * $queryWithTransaction->setTransaction($myTransaction);
 *
 * $resultWithEntries = $queryWithTransaction->execute();
 *
 * $queryWithOutTransaction = new Query($phql, $di);
 * $resultWithOutEntries = $queryWithTransaction->execute();
 * ```
 */
class Query implements QueryInterface, InjectionAwareInterface {

    const TYPE_DELETE = 303;
    const TYPE_INSERT = 306;
    const TYPE_SELECT = 309;
    const TYPE_UPDATE = 300;

    protected $ast;
    protected $bindParams;
    protected $bindTypes;
    protected $cache;
    protected $cacheOptions;
    protected $container;
    protected $enableImplicitJoins;
    protected $intermediate;
    protected $manager;
    protected $metaData;
    protected $models;
    protected $modelsInstances;
    protected $nestingLevel = -1;
    protected $phql;
    protected $sharedLock;
    protected $sqlAliases;
    protected $sqlAliasesModels;
    protected $sqlAliasesModelsInstances;
    protected $sqlColumnAliases = [];
    protected $sqlModelsAliases;
    protected $type;
    protected bool $uniqueRow;
    //TODO request local cache
    static protected $_irPhqlCache;

    /**
     * TransactionInterface so that the query can wrap a transaction
     * around batch updates and intermediate selects within the transaction.
     * however if a model got a transaction set inside it will use the local
     * transaction instead of this one
     */
    protected $_transaction;

    /**
     * Phalcon\Mvc\Model\Query constructor
     */
    public function __construct(string $phql = null, DiInterface $container = null, array $options = []) {

        $this->phql = $phql;

        if (is_object($container)) {
            $this->setDI($container);
        }
        $enableImplicitJoins = $options["enable_implicit_joins"] ?? null;

        if ($enableImplicitJoins !== null) {
            $this->enableImplicitJoins = $enableImplicitJoins;
        } else {
            $this->enableImplicitJoins = \globals_get("orm.enable_implicit_joins");
        }
    }

    /** return {get} of Query transaction */
    public function getTransaction(): object {
        return $this->$_transaction;
    }

    /**
     * Sets the dependency injection container
     */
    public function setDI(DiInterface $container): void {

        $manager = $container->getShared("modelsManager");

        if (!is_object($manager)) {
            throw new Exception("Injected service 'modelsManager' is invalid");
        }

        $metaData = $container->getShared("modelsMetadata");

        if (!is_object($metaData)) {
            throw new Exception("Injected service 'modelsMetaData' is invalid");
        }

        $this->manager = $manager;
        $this->metaData = $metaData;

        $this->container = $container;
    }

    /**
     * Returns the dependency injection container
     */
    public function getDI(): DiInterface {
        return $this->container;
    }

    /**
     * Tells to the query if only the first row in the resultset must be
     * returned
     */
    public function setUniqueRow(bool $uniqueRow): QueryInterface {
        $this->uniqueRow = $uniqueRow;

        return $this;
    }

    /**
     * Check if the query is programmed to get only the first row in the
     * resultset
     */
    public function getUniqueRow(): bool {
        return $this->uniqueRow;
    }

    /**
     * Replaces the model's name to its source name in a qualified-name
     * expression
     */
    final protected function _getQualified(array $expr): array {
        $columnName = $expr["name"];

        $nestingLevel = $this->nestingLevel;

        /**
         * Check if the qualified name is a column alias
         */
        $sqlColumnAliases = $this->sqlColumnAliases[$nestingLevel] ?? [];
        $columnDomain = $expr["domain"] ?? null;

        if (isset($sqlColumnAliases[$columnName])) {
            if (empty($columnDomain)) {
                return [
                    "type" => "qualified",
                    "name" => $columnName
                ];
            }
        }
        $metaData = $this->metaData;

        /**
         * Check if the qualified name has a domain
         */
        if ($columnDomain !== null) {

            $sqlAliases = $this->sqlAliases;

            /**
             * The column has a domain, we need to check if it's an alias
             */
            $source = $sqlAliases[$columnDomain] ?? null;
            if ($source === null) {

                throw new Exception(
                    "Unknown model or alias '" . $columnDomain . "' (11), when preparing: " . $this->phql
                );
            }

            /**
             * Change the selected column by its real name on its mapped table
             */
            if (\globals_get("orm.column_renaming")) {
                /**
                 * Retrieve the corresponding model by its alias
                 */
                $sqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;

                /**
                 * We need the model instance to retrieve the reversed column
                 * map
                 */
                $model = $sqlAliasesModelsInstances[$columnDomain] ?? null;
                if ($model === null) {

                    throw new Exception(
                                    "There is no model related to model or alias '" . $columnDomain . "', when executing: " . $this->phql
                    );
                }

                $columnMap = $metaData->getReverseColumnMap($model);
            } else {
                $columnMap = null;
            }

            if (is_array($columnMap)) {
                $realColumnName = $columnMap[$columnName] ?? null;
                if ($realColumnName === null) {

                    throw new Exception(
                                    "Column '" . $columnName . "' doesn't belong to the model or alias '" . $columnDomain . "', when executing: " . $this->phql
                    );
                }
            } else {
                $realColumnName = $columnName;
            }
        } else {
            /**
             * If the column IR doesn't have a domain, we must check for
             * ambiguities
             */
            $number = 0;
            $hasModel = false;

            foreach ($this->modelsInstances as $model) {
                /**
                 * Check if the attribute belongs to the current model
                 */
                if ($metaData->hasAttribute($model, $columnName)) {
                    $number++;

                    if ($number > 1) {
                        throw new Exception(
                                        "The column '" . $columnName . "' is ambiguous, when preparing: " . $this->phql
                        );
                    }

                    $hasModel = $model;
                }
            }

            /**
             * After check in every model, the column does not belong to any of
             * the selected models
             */
            if ($hasModel === false) {
                throw new Exception(
                                "Column '" . $columnName . "' doesn't belong to any of the selected models (1), when preparing: " . $this->phql
                );
            }

            /**
             * Check if the models property is correctly prepared
             */
            $models = $this->models;

            if (!is_array($models)) {
                throw new Exception(
                                "The models list was not loaded correctly"
                );
            }

            /**
             * Obtain the model's source from the models list
             */
            $className = get_class($hasModel);

            $source = $models[$className] ?? null;
            if ($source === null) {

                throw new Exception(
                                "Can't obtain model's source from models list: '" . $className .
                                "', when preparing: " . $this->phql
                );
            }

            /**
             * Rename the column
             */
            if (\globals_get("orm.column_renaming")) {
                $columnMap = $metaData->getReverseColumnMap($hasModel);
            } else {
                $columnMap = null;
            }

            if (is_array($columnMap)) {
                /**
                 * The real column name is in the column map
                 */
                $realColumnName = $columnMap[$columnName] ?? null;
                if ($realColumnName === null) {

                    throw new Exception(
                                    "Column '" . $columnName . "' doesn't belong to any of the selected models (3), when preparing: " . $this->phql
                    );
                }
            } else {
                $realColumnName = $columnName;
            }
        }

        /**
         * Create an array with the qualified info
         */
        return [
            "type" => "qualified",
            "domain" => $source,
            "name" => $realColumnName,
            "balias" => $columnName
        ];
    }

    /**
     * Resolves an expression in a single call argument
     */
    final protected function _getCallArgument(array $argument): array {
        if ($argument["type"] == PHQL_T_STARALL) {
            return [
                "type" => "all"
            ];
        }

        return $this->_getExpression($argument);
    }

    /**
     * Resolves an expression in a single call argument
     */
    final protected function _getCaseExpression(array $expr): array {

        $whenClauses = [];

        foreach ($expr["right"] as $whenExpr) {
            if (isset($whenExpr["right"])) {
                $whenClauses[] = [
                    "type" => "when",
                    "expr" => $this->_getExpression($whenExpr["left"]),
                    "then" => $this->_getExpression($whenExpr["right"])
                ];
            } else {
                $whenClauses[] = [
                    "type" => "else",
                    "expr" => $this->_getExpression($whenExpr["left"])
                ];
            }
        }

        return [
            "type" => "case",
            "expr" => $this->_getExpression($expr["left"]),
            "when-clauses" => $whenClauses
        ];
    }

    /**
     * Resolves an expression in a single call argument
     */
    final protected function _getFunctionCall(array $expr): array {

        $arguments = $expr["arguments"] ?? null;
        if ($arguments !== null) {

            if (isset($expr["distinct"])) {
                $distinct = 1;
            } else {
                $distinct = 0;
            }

            if (isset($arguments[0])) {
                // There are more than one argument
                $functionArgs = [];

                foreach ($arguments as $argument) {
                    $functionArgs[] = $this->_getCallArgument($argument);
                }
            } else {
                // There is only one argument
                $functionArgs = [
                    $this->_getCallArgument($arguments)
                ];
            }

            if ($distinct) {
                return [
                    "type" => "functionCall",
                    "name" => $expr["name"],
                    "arguments" => $functionArgs,
                    "distinct" => $distinct
                ];
            } else {
                return [
                    "type" => "functionCall",
                    "name" => $expr["name"],
                    "arguments" => $functionArgs
                ];
            }
        }

        return [
            "type" => "functionCall",
            "name" => $expr["name"]
        ];
    }

    /**
     * Resolves an expression from its intermediate code into a string
     */
    final protected function _getExpression(array $expr, bool $quoting = true): string {
        $left = null;
        $right = null;

        $exprType = $expr["type"] ?? null;
        if ($exprType !== null) {

            $tempNotQuoting = true;

            if ($exprType != PHQL_T_CASE) {
                /**
                 * Resolving the left part of the expression if any
                 */
                $exprLeft = $expr["left"] ?? null;
                if ($exprLeft !== null) {

                    $left = $this->_getExpression($exprLeft, $tempNotQuoting);
                }

                /**
                 * Resolving the right part of the expression if any
                 */
                $exprRight = $expr["right"] ?? null;
                if ($exprRight !== null) {

                    $right = $this->_getExpression($exprRight, $tempNotQuoting);
                }
            }

            /**
             * Every node in the AST has a unique integer type
             */
            switch ($exprType) {
                case PHQL_T_LESS:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "<",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_EQUALS:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "=",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_GREATER:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => ">",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_NOTEQUALS:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "<>",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_LESSEQUAL:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "<=",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_GREATEREQUAL:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => ">=",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_AND:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "AND",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_OR:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "OR",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_QUALIFIED:
                    $exprReturn = $this->_getQualified($expr);
                    break;

                case PHQL_T_ADD:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "+",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_SUB:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "-",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_MUL:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "*",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_DIV:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "/",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_MOD:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "%",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_BITWISE_AND:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "&",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_BITWISE_OR:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "|",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_ENCLOSED:
                case PHQL_T_SUBQUERY:
                    $exprReturn = [
                        "type" => "parentheses",
                        "left" => $left
                    ];

                    break;

                case PHQL_T_MINUS:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => "-",
                        "right" => $right
                    ];

                    break;

                case PHQL_T_INTEGER:
                case PHQL_T_DOUBLE:
                case PHQL_T_HINTEGER:
                    $exprReturn = [
                        "type" => "literal",
                        "value" => $expr["value"]
                    ];

                    break;

                case PHQL_T_TRUE:
                    $exprReturn = [
                        "type" => "literal",
                        "value" => "TRUE"
                    ];

                    break;

                case PHQL_T_FALSE:
                    $exprReturn = [
                        "type" => "literal",
                        "value" => "FALSE"
                    ];

                    break;

                case PHQL_T_STRING:
                    $value = $expr["value"];

                    if (quoting) {
                        /**
                         * Check if static literals have single quotes and
                         * escape them
                         */
                        if (memstr($value, "'")) {
                            $escapedValue = phalcon_orm_singlequotes($value);
                        } else {
                            $escapedValue = $value;
                        }

                        $exprValue = "'" . $escapedValue . "'";
                    } else {
                        $exprValue = $value;
                    }

                    $exprReturn = [
                        "type" => "literal",
                        "value" => $exprValue
                    ];

                    break;

                case PHQL_T_NPLACEHOLDER:
                    $exprReturn = [
                        "type" => "placeholder",
                        "value" => str_replace("?", ":", $expr["value"])
                    ];

                    break;

                case PHQL_T_SPLACEHOLDER:
                    $exprReturn = [
                        "type" => "placeholder",
                        "value" => ":" . $expr["value"]
                    ];

                    break;

                case PHQL_T_BPLACEHOLDER:
                    $value = $expr["value"];

                    if (memstr($value, ":")) {
                        $valueParts = explode(":", $value);
                        $name = $valueParts[0];
                        $bindType = $valueParts[1];

                        switch ($bindType) {

                            case "str":
                                $this->bindTypes[$name] = Column::BIND_PARAM_STR;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "int":
                                $this->bindTypes[$name] = Column::BIND_PARAM_INT;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "double":
                                $this->bindTypes[$name] = Column::BIND_PARAM_DECIMAL;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "bool":
                                $this->bindTypes[$name] = Column::BIND_PARAM_BOOL;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "blob":
                                $this->bindTypes[$name] = Column::BIND_PARAM_BLOB;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "null":
                                $this->bindTypes[$name] = Column::BIND_PARAM_NULL;

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name
                                ];

                                break;

                            case "array":
                            case "array-str":
                            case "array-int":
                                $bind = $this->bindParams[$name] ?? null;
                                if ($bind === null) {

                                    throw new Exception(
                                                    "Bind value is required for array type placeholder: " . $name
                                    );
                                }

                                if (!is_array($bind)) {
                                    throw new Exception(
                                                    "Bind type requires an array in placeholder: " . $name
                                    );
                                }

                                if (count($bind) < 1) {
                                    throw new Exception(
                                                    "At least one value must be bound in placeholder: " . $name
                                    );
                                }

                                $exprReturn = [
                                    "type" => "placeholder",
                                    "value" => ":" . $name,
                                    "rawValue" => $name,
                                    "times" => count($bind)
                                ];

                                break;

                            default:
                                throw new Exception(
                                                "Unknown bind type: " . $bindType
                                );
                        }
                    } else {
                        $exprReturn = [
                            "type" => "placeholder",
                            "value" => ":" . $value
                        ];
                    }

                    break;

                case PHQL_T_NULL:
                    $exprReturn = [
                        "type" => "literal",
                        "value" => "NULL"
                    ];

                    break;

                case PHQL_T_LIKE:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "LIKE",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_NLIKE:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "NOT LIKE",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_ILIKE:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "ILIKE",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_NILIKE:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "NOT ILIKE",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_NOT:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => "NOT ",
                        "right" => $right
                    ];

                    break;

                case PHQL_T_ISNULL:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => " IS NULL",
                        "left" => $left
                    ];

                    break;

                case PHQL_T_ISNOTNULL:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => " IS NOT NULL",
                        "left" => $left
                    ];

                    break;

                case PHQL_T_IN:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "IN",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_NOTIN:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "NOT IN",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_EXISTS:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => "EXISTS",
                        "right" => $right
                    ];

                    break;

                case PHQL_T_DISTINCT:
                    $exprReturn = [
                        "type" => "unary-op",
                        "op" => "DISTINCT ",
                        "right" => $right
                    ];

                    break;

                case PHQL_T_BETWEEN_NOT:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "BETWEEN NOT",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_BETWEEN:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "BETWEEN",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_AGAINST:
                    $exprReturn = [
                        "type" => "binary-op",
                        "op" => "AGAINST",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_CAST:
                    $exprReturn = [
                        "type" => "cast",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_CONVERT:
                    $exprReturn = [
                        "type" => "convert",
                        "left" => $left,
                        "right" => $right
                    ];

                    break;

                case PHQL_T_RAW_QUALIFIED:
                    $exprReturn = [
                        "type" => "literal",
                        "value" => $expr["name"]
                    ];

                    break;

                case PHQL_T_FCALL:
                    $exprReturn = $this->_getFunctionCall($expr);

                    break;

                case PHQL_T_CASE:
                    $exprReturn = $this->_getCaseExpression($expr);

                    break;

                case PHQL_T_SELECT:
                    $exprReturn = [
                        "type" => "select",
                        "value" => $this->_prepareSelect($expr, true)
                    ];

                    break;

                default:
                    throw new Exception("Unknown $expression type " . $exprType);
            }

            return $exprReturn;
        }

        /**
         * It's a qualified column
         */
        if (isset($expr["domain"])) {
            return $this->_getQualified($expr);
        }

        /**
         * If the expression doesn't have a type it's a list of nodes
         */
        if (isset($expr[0])) {
            $listItems = [];

            foreach ($expr as $exprListItem) {
                $listItems[] = $this->_getExpression($exprListItem);
            }

            return [
                "type" => "list",
                $listItems
            ];
        }

        throw new Exception("Unknown $expression");
    }

    /**
     * Resolves a column from its intermediate representation into an array
     * used to determine if the resultset produced is simple or complex
     */
    final protected function _getSelectColumn(array $column): array {
        $columnType = $column["type"] ?? null;
        if ($columnType === null) {

            throw new Exception("Corrupted SELECT AST");
        }

        $sqlColumns = [];

        /**
         * Check if column is eager loaded
         */
        $eager = $column["eager"] ?? null;

        /**
         * Check for select * (all)
         */
        if ($columnType == PHQL_T_STARALL) {
            foreach ($this->models as $modelName => $source) {
                $sqlColumn = [
                    "type" => "object",
                    "model" => $modelName,
                    "column" => $source,
                    "balias" => lcfirst($modelName)
                ];

                if ($eager !== null) {
                    $sqlColumn["eager"] = $eager;
                    $sqlColumn["eagerType"] = $column["eagerType"];
                }

                $sqlColumns[] = $sqlColumn;
            }

            return $sqlColumns;
        }

        if (!isset($column["column"])) {
            throw new Exception("Corrupted SELECT AST");
        }

        /**
         * Check if selected column is qualified.*, ex: robots.*
         */
        if ($columnType == PHQL_T_DOMAINALL) {
            $sqlAliases = $this->sqlAliases;

            /**
             * We only allow the alias.*
             */
            $columnDomain = $column["column"];

            $source = $sqlAliases[$columnDomain] ?? null;
            if ($source === null) {

                throw new Exception(
                                "Unknown model or alias '" . $columnDomain . "' (2), when preparing: " . $this->phql
                );
            }

            /**
             * Get the SQL alias if any
             */
            $sqlColumnAlias = $source;

            $preparedAlias = $column["balias"] ?? null;

            /**
             * Get the real source name
             */
            $sqlAliasesModels = $this->sqlAliasesModels;
            $modelName = $sqlAliasesModels[$columnDomain];

            if (!is_string($preparedAlias)) {

                /**
                 * If the best alias is the $model name, we lowercase the first
                 * letter
                 */
                if ($columnDomain == $modelName) {
                    $preparedAlias = lcfirst($modelName);
                } else {
                    $preparedAlias = $columnDomain;
                }
            }

            /**
             * Each item is a complex type returning a complete object
             */
            $sqlColumn = [
                "type" => "object",
                "model" => $modelName,
                "column" => $sqlColumnAlias,
                "balias" => $preparedAlias
            ];

            if ($eager !== null) {
                $sqlColumn["eager"] = $eager;
                $sqlColumn["eagerType"] = $column["eagerType"];
            }

            $sqlColumns[] = $sqlColumn;

            return $sqlColumns;
        }

        /**
         * Check for columns qualified and not qualified
         */
        if ($columnType == PHQL_T_EXPR) {

            /**
             * The $sql_column is a scalar type returning a simple string
             */
            $sqlColumn = ["type" => "scalar"];
            $columnData = $column["column"];
            $sqlExprColumn = $this->_getExpression($columnData);

            /**
             * Create balias and $sqlAlias
             */
            $balias = $sqlExprColumn["balias"] ?? null;
            if ($balias !== null) {

                $sqlColumn["balias"] = $balias;
                $sqlColumn["$sqlAlias"] = $balias;
            }

            if ($eager !== null) {
                $sqlColumn["eager"] = $eager;
                $sqlColumn["eagerType"] = $column["eagerType"];
            }

            $sqlColumn["column"] = $sqlExprColumn;
            $sqlColumns[] = $sqlColumn;

            return $sqlColumns;
        }

        throw new Exception("Unknown type of column " . $columnType);
    }

    /**
     * Resolves a table in a SELECT statement checking if the model exists
     *
     * @return string
     */
    final protected function _getTable(ManagerInterface $manager, array $qualifiedName) {

        $modelName = $qualifiedName["name"] ?? null;
        if ($modelName === null) {

            throw new Exception("Corrupted SELECT AST");
        }

        $model = $manager->load($modelName);
        $source = $model->getSource();
        $schema = $model->getSchema();

        if ($schema) {
            return [$schema, $source];
        }

        return $source;
    }

    /**
     * Resolves a JOIN clause checking if the associated models exist
     */
    final protected function _getJoin(ManagerInterface $manager, array $join): array {

        $qualified = $join["qualified"] ?? null;
        if ($qualified !== null) {

            if ($qualified["type"] == PHQL_T_QUALIFIED) {
                $modelName = $qualified["name"];

                $model = $manager->load($modelName);
                $source = $model->getSource();
                $schema = $model->getSchema();

                return [
                    "schema" => $schema,
                    "$source" => $source,
                    "modelName" => $modelName,
                    "model" => $model
                ];
            }
        }

        throw new Exception("Corrupted SELECT AST");
    }

    /**
     * Resolves a JOIN type
     */
    final protected function _getJoinType(array $join): string {
        $type = $join["type"] ?? null;
        if ($type === null) {
            throw new Exception("Corrupted SELECT AST");
        }

        switch ($type) {
            case PHQL_T_INNERJOIN:
                return "INNER";

            case PHQL_T_LEFTJOIN:
                return "LEFT";

            case PHQL_T_RIGHTJOIN:
                return "RIGHT";

            case PHQL_T_CROSSJOIN:
                return "CROSS";

            case PHQL_T_FULLJOIN:
                return "FULL OUTER";
        }

        throw new Exception(
                        "Unknown join type " . $type . ", when preparing: " . $this->phql
        );
    }

    /**
     * Resolves joins involving has-one/belongs-to/has-many relations
     *
     * @param string joinSource
     */
    final protected function _getSingleJoin(string $joinType, $joinSource, string $modelAlias, string $joinAlias, RelationInterface $relation): array {
        /**
         * Local fields in the 'from' relation
         */
        $fields = $relation->getFields();

        /**
         * Referenced fields in the joined relation
         */
        $referencedFields = $relation->getReferencedFields();

        if (!is_array($fields)) {
            /**
             * Create the left part of the expression
             * Create a binary operation for the join conditions
             * Create the right part of the expression
             */
            $sqlJoinConditions = [
                [
                    "type" => "binary-op",
                    "op" => "=",
                    "left" => $this->_getQualified(
                            [
                                "type" => PHQL_T_QUALIFIED,
                                "domain" => $modelAlias,
                                "name" => $fields
                            ]
                    ),
                    "right" => $this->_getQualified(
                            [
                                "type" => "qualified",
                                "domain" => $joinAlias,
                                "name" => $referencedFields
                            ]
                    )
                ]
            ];
        } else {
            /**
             * Resolve the compound operation
             */
            $sqlJoinPartialConditions = [];

            foreach ($fields as $position => $field) {
                /**
                 * Get the referenced field in the same position
                 */
                $referencedField = $referencedFields[$position] ?? null;
                if ($referencedField === null) {

                    throw new Exception(
                                    "The number of fields must be equal to the number of referenced fields in join " . $modelAlias . "-" . $joinAlias . ", when preparing: " . $this->phql
                    );
                }

                /**
                 * Create the left part of the expression
                 * Create the right part of the expression
                 * Create a binary operation for the join conditions
                 */
                $sqlJoinPartialConditions[] = [
                    "type" => "binary-op",
                    "op" => "=",
                    "left" => $this->_getQualified(
                            [
                                "type" => PHQL_T_QUALIFIED,
                                "domain" => $modelAlias,
                                "name" => $field
                            ]
                    ),
                    "right" => $this->_getQualified(
                            [
                                "type" => "qualified",
                                "domain" => $joinAlias,
                                "name" => $referencedField
                            ]
                    )
                ];
            }
        }

        /**
         * A single join
         */
        return [
            "type" => $joinType,
            "source" => $joinSource,
            "conditions" => $sqlJoinConditions
        ];
    }

    /**
     * Resolves joins involving many-to-many relations
     *
     * @param string joinSource
     */
    final protected function _getMultiJoin(string $joinType, string $joinSource, string $modelAlias, string $joinAlias, RelationInterface $relation): array {
        $sqlJoins = [];

        /**
         * Local fields in the 'from' relation
         */
        $fields = $relation->getFields();

        /**
         * Referenced fields in the joined relation
         */
        $referencedFields = $relation->getReferencedFields();

        /**
         * Intermediate model
         */
        $intermediateModelName = $relation->getIntermediateModel();

        $manager = $this->manager;

        /**
         * Get the intermediate model instance
         */
        $intermediateModel = $manager->load($intermediateModelName);

        /**
         * Source of the related model
         */
        $intermediateSource = $intermediateModel->getSource();

        /**
         * Schema of the related model
         */
        $intermediateSchema = $intermediateModel->getSchema();

        //intermediateFullSource = [intermediateSchema, $intermediateSource];

        /**
         * Update the internal $sqlAliases to set up the intermediate model
         */
        $this->sqlAliases[$intermediateModelName] = $intermediateSource;

        /**
         * Update the internal $sqlAliasesModelsInstances to rename columns if
         * necessary
         */
        $this->sqlAliasesModelsInstances[$intermediateModelName] = $intermediateModel;

        /**
         * Fields that join the 'from' model with the 'intermediate' model
         */
        $intermediateFields = $relation->getIntermediateFields();

        /**
         * Fields that join the 'intermediate' model with the intermediate model
         */
        $intermediateReferencedFields = $relation->getIntermediateReferencedFields();

        /**
         * Intermediate model
         */
        $referencedModelName = $relation->getReferencedModel();

        if (is_array($fields)) {
            foreach ($fields as $field => $position) {
                if (!isset($referencedFields[$position])) {
                    throw new Exception(
                                    "The number of fields must be equal to the number of referenced fields in join " . $modelAlias . "-" . $joinAlias . ", when preparing: " . $this->phql
                    );
                }

                /**
                 * Get the referenced field in the same $position
                 */
                $intermediateField = $intermediateFields[$position];

                /**
                 * Create a binary operation for the join conditions
                 */
                $sqlEqualsJoinCondition = [
                    "type" => "binary-op",
                    "op" => "=",
                    "left" => $this->_getQualified(
                            [
                                "type" => PHQL_T_QUALIFIED,
                                "domain" => $modelAlias,
                                "name" => $field
                            ]
                    ),
                    "right" => $this->_getQualified(
                            [
                                "type" => "qualified",
                                "domain" => $joinAlias,
                                "name" => $referencedFields
                            ]
                    )
                ];

                //let $sqlJoinPartialConditions[] = $sqlEqualsJoinCondition;
            }
        } else {

            /**
             * Create the left part of the expression
             * Create the right part of the expression
             * Create a binary operation for the join conditions
             * A single join
             */
            $sqlJoins = [
                [
                    "type" => $joinType,
                    "source" => [$intermediateSource, $intermediateSchema],
                    "conditions" => [
                        [
                            "type" => "binary-op",
                            "op" => "=",
                            "left" => $this->_getQualified(
                                    [
                                        "type" => PHQL_T_QUALIFIED,
                                        "domain" => $modelAlias,
                                        "name" => $fields
                                    ]
                            ),
                            "right" => $this->_getQualified(
                                    [
                                        "type" => "qualified",
                                        "domain" => $intermediateModelName,
                                        "name" => $intermediateFields
                                    ]
                            )
                        ]
                    ]
                ],
                /**
                 * Create the left part of the expression
                 * Create the right part of the expression
                 * Create a binary operation for the join conditions
                 * A single join
                 */
                [
                    "type" => $joinType,
                    "source" => $joinSource,
                    "conditions" => [
                        [
                            "type" => "binary-op",
                            "op" => "=",
                            "left" => $this->_getQualified(
                                    [
                                        "type" => PHQL_T_QUALIFIED,
                                        "domain" => $intermediateModelName,
                                        "name" => $intermediateReferencedFields
                                    ]
                            ),
                            "right" => $this->_getQualified(
                                    [
                                        "type" => "qualified",
                                        "domain" => $referencedModelName,
                                        "name" => $referencedFields
                                    ]
                            )
                        ]
                    ]
                ]
            ];
        }

        return $sqlJoins;
    }

    /**
     * Processes the JOINs in the query returning an internal representation for
     * the database dialect
     */
    final protected function _getJoins(array $select): array {
        $models = $this->models;
        $sqlAliases = $this->sqlAliases;
        $sqlAliasesModels = $this->sqlAliasesModels;
        $sqlModelsAliases = $this->sqlModelsAliases;
        $sqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;
        $modelsInstances = $this->modelsInstances;
        $fromModels = $models;

        $sqlJoins = [];
        $joinModels = [];
        $joinSources = [];
        $joinTypes = [];
        $joinPreCondition = [];
        $joinPrepared = [];

        $manager = $this->manager;

        $tables = $select["tables"];

        if (!isset($tables[0])) {
            $selectTables = [$tables];
        } else {
            $selectTables = $tables;
        }

        $joins = $select["joins"];

        if (!isset($joins[0])) {
            $selectJoins = [$joins];
        } else {
            $selectJoins = $joins;
        }

        foreach ($selectJoins as $joinItem) {

            /**
             * Check join alias
             */
            $joinData = $this->_getJoin($manager, $joinItem);
            $source = $joinData["source"];
            $schema = $joinData["schema"];
            $model = $joinData["model"];
            $realModelName = $joinData["modelName"];
            $completeSource = [$source, $schema];

            /**
             * Check join alias
             */
            $joinType = $this->_getJoinType($joinItem);

            /**
             * Process join alias
             */
            $aliasExpr = $joinItem["alias"] ?? null;
            if ($aliasExpr !== null) {

                $alias = $aliasExpr["name"];

                /**
                 * Check if alias is unique
                 */
                if (isset($joinModels[$alias])) {
                    throw new Exception(
                                    "Cannot use '" . $alias . "' as join alias because it was already used, when preparing: " . $this->phql
                    );
                }

                /**
                 * Add the alias to the source
                 */
                $completeSource[] = $alias;

                /**
                 * Set the join type
                 */
                $joinTypes[$alias] = $joinType;

                /**
                 * Update alias: alias
                 */
                $sqlAliases[$alias] = $alias;

                /**
                 * Update model: alias
                 */
                $joinModels[$alias] = $realModelName;

                /**
                 * Update model: alias
                 */
                $sqlModelsAliases[$realModelName] = $alias;

                /**
                 * Update model: model
                 */
                $sqlAliasesModels[$alias] = $realModelName;

                /**
                 * Update alias: model
                 */
                $sqlAliasesModelsInstances[$alias] = $model;

                /**
                 * Update model: alias
                 */
                $models[$realModelName] = $alias;

                /**
                 * Complete source related to a model
                 */
                $joinSources[$alias] = $completeSource;

                /**
                 * Complete source related to a model
                 */
                $joinPrepared[$alias] = $joinItem;
            } else {
                /**
                 * Check if alias is unique
                 */
                if (isset($joinModels[$realModelName])) {
                    throw new Exception(
                                    "Cannot use '" . $realModelName . "' as join alias because it was already used, when preparing: " . $this->phql
                    );
                }

                /**
                 * Set the join type
                 */
                $joinTypes[$realModelName] = $joinType;

                /**
                 * Update model: source
                 */
                $sqlAliases[$realModelName] = $source;

                /**
                 * Update model: $source
                 */
                $joinModels[$realModelName] = $source;

                /**
                 * Update model: model
                 */
                $sqlModelsAliases[$realModelName] = $realModelName;

                /**
                 * Update model: model
                 */
                $sqlAliasesModels[$realModelName] = $realModelName;

                /**
                 * Update model: model instance
                 */
                $sqlAliasesModelsInstances[$realModelName] = $model;

                /**
                 * Update model: $source
                 */
                $models[$realModelName] = $source;

                /**
                 * Complete source related to a model
                 */
                $joinSources[$realModelName] = $completeSource;

                /**
                 * Complete source related to a model
                 */
                $joinPrepared[$realModelName] = $joinItem;
            }

            $modelsInstances[$realModelName] = $model;
        }

        /**
         * Update temporary properties
         */
        $this->models = $models;
        $this->sqlAliases = $sqlAliases;
        $this->sqlAliasesModels = $sqlAliasesModels;
        $this->sqlModelsAliases = $sqlModelsAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;
        $this->modelsInstances = $modelsInstances;

        foreach ($joinPrepared as $joinAliasName => $joinItem) {

            /**
             * Check for predefined conditions
             */
            $joinExpr = $joinItem["conditions"] ?? null;
            if ($joinExpr !== null) {

                $joinPreCondition[$joinAliasName] = $this->_getExpression($joinExpr);
            }
        }

        /**
         * Skip all implicit joins if the option is not enabled
         */
        if (!$this->enableImplicitJoins) {
            foreach ($joinPrepared as $joinAliasName => $__) {
                $joinType = $joinTypes[$joinAliasName];
                $joinSource = $joinSources[$joinAliasName];
                $preCondition = $joinPreCondition[$joinAliasName];
                $sqlJoins[] = [
                    "type" => $joinType,
                    "source" => $joinSource,
                    "conditions" => [$preCondition]
                ];
            }

            return $sqlJoins;
        }

        /**
         * Build the list of tables used in the SELECT clause
         */
        $fromModels = [];

        foreach ($selectTables as $tableItem) {
            $fromModels[$tableItem["qualifiedName"]["name"]] = true;
        }

        /**
         * Create join relationships dynamically
         */
        foreach ($fromModels as $fromModelName => $_) {
            foreach ($joinModels as $joinAlias => $joinModel) {
                /**
                 * Real source name for joined model
                 */
                $joinSource = $joinSources[$joinAlias];

                /**
                 * Join type is: LEFT, RIGHT, INNER, etc
                 */
                $joinType = $joinTypes[$joinAlias];

                /**
                 * Check if the model already have pre-defined conditions
                 */
                $preCondition = $joinPreCondition[$joinAlias] ?? null;
                if ($preCondition === null) {

                    /**
                     * Get the model name from its source
                     */
                    $modelNameAlias = $sqlAliasesModels[$joinAlias];

                    /**
                     * Check if the joined model is an alias
                     */
                    $relation = $manager->getRelationByAlias(
                            $fromModelName,
                            $modelNameAlias
                    );

                    if ($relation === false) {
                        /**
                         * Check for relations between $models
                         */
                        $relations = $manager->getRelationsBetween(
                                $fromModelName,
                                $modelNameAlias
                        );

                        if (is_array($relations)) {
                            /**
                             * More than one relation must throw an exception
                             */
                            if (count($relations) != 1) {
                                throw new Exception(
                                                "There is more than one relation between models '" . $fromModelName . "' and '" . $joinModel . "', the join must be done using an alias, when preparing: " . $this->phql
                                );
                            }

                            /**
                             * Get the first relationship
                             */
                            $relation = $relations[0];
                        }
                    }

                    /*
                     * Valid relations are objects
                     */
                    if (is_object($relation)) {
                        /**
                         * Get the related model alias of the left part
                         */
                        $modelAlias = $sqlModelsAliases[$fromModelName];

                        /**
                         * Generate the conditions based on the type of join
                         */
                        if (!$relation->isThrough()) {
                            $sqlJoin = $this->_getSingleJoin(
                                    $joinType,
                                    $joinSource,
                                    $modelAlias,
                                    $joinAlias,
                                    $relation
                            );
                        } else {
                            $sqlJoin = $this->_getMultiJoin(
                                    $joinType,
                                    $joinSource,
                                    $modelAlias,
                                    $joinAlias,
                                    $relation
                            );
                        }

                        /**
                         * Append or merge joins
                         */
                        if (isset($sqlJoin[0])) {
                            foreach ($sqlJoin as $sqlJoinItem) {
                                $sqlJoins[] = $sqlJoinItem;
                            }
                        } else {
                            $sqlJoins[] = $sqlJoin;
                        }
                    } else {
                        /**
                         * Join without conditions because no relation has been
                         * found between the models
                         */
                        $sqlJoins[] = [
                            "type" => $joinType,
                            "source" => $joinSource,
                            "conditions" => []
                        ];
                    }
                } else {
                    /**
                     * Get the conditions established by the developer
                     * Join with conditions established by the developer
                     */
                    $sqlJoins[] = [
                        "type" => $joinType,
                        "source" => $joinSource,
                        "conditions" => [$preCondition]
                    ];
                }
            }
        }

        return $sqlJoins;
    }

    /**
     * Returns a processed order clause for a SELECT statement
     *
     * @param array|string order
     */
    final protected function _getOrderClause($order): array {
        if (!isset($order[0])) {
            $orderColumns = [$order];
        } else {
            $orderColumns = $order;
        }

        $orderParts = [];

        foreach ($orderColumns as $orderItem) {
            $orderPartExpr = $this->_getExpression(
                    $orderItem["column"]
            );

            /**
             * Check if the order has a predefined ordering mode
             */
            $orderSort = $orderItem["sort"] ?? null;
            if ($orderSort !== null) {

                if ($orderSort == PHQL_T_ASC) {
                    $orderPartSort = [$orderPartExpr, "ASC"];
                } else {
                    $orderPartSort = [$orderPartExpr, "DESC"];
                }
            } else {
                $orderPartSort = [$orderPartExpr];
            }

            $orderParts[] = $orderPartSort;
        }

        return $orderParts;
    }

    /**
     * Returns a processed group clause for a SELECT statement
     */
    final protected function _getGroupClause(array $group): array {
        if (isset($group[0])) {
            /**
             * The select is grouped by several columns
             */
            $groupParts = [];

            foreach ($group as $groupItem) {
                $groupParts[] = $this->_getExpression($groupItem);
            }
        } else {
            $groupParts = [
                $this->_getExpression($group)
            ];
        }

        return $groupParts;
    }

    /**
     * Returns a processed limit clause for a SELECT statement
     */
    final protected function _getLimitClause(array $limitClause): array {
        $limit = [];

        $number = $limitClause["number"] ?? null;
        if ($number !== null) {

            $limit["number"] = $this->_getExpression($number);
        }

        $offset = $limitClause["offset"] ?? null;
        if ($offset !== null) {

            $limit["offset"] = $this->_getExpression($offset);
        }

        return $limit;
    }

    /**
     * Analyzes a SELECT intermediate code and produces an array to be executed later
     */
    final protected function _prepareSelect($ast = null, bool $merge = false): array {
        if (empty($ast)) {
            $ast = $this->ast;
        }

        $select = $ast["select"] ?? null;
        if ($select === null) {

            $select = $ast;
        }

        $tables = $select["tables"] ?? null;
        if ($tables === null) {

            throw new Exception("Corrupted SELECT AST");
        }

        $columns = $select["columns"] ?? null;
        if ($columns === null) {

            throw new Exception("Corrupted SELECT AST");
        }

        $this->nestingLevel++;

        /**
         * $sqlModels is an array of the models to be used in the query
         */
        $sqlModels = [];

        /**
         * $sqlTables is an array of the mapped models sources to be used in the
         * query
         */
        $sqlTables = [];

        /**
         * $sqlColumns is an array of every column expression
         */
        $sqlColumns = [];

        /**
         * $sqlAliases is a map from aliases to mapped sources
         */
        $sqlAliases = [];

        /**
         * $sqlAliasesModels is a map from aliases to model names
         */
        $sqlAliasesModels = [];

        /**
         * $sqlAliasesModels is a map from model names to aliases
         */
        $sqlModelsAliases = [];

        /**
         * $sqlAliasesModelsInstances is a map from aliases to model instances
         */
        $sqlAliasesModelsInstances = [];

        /**
         * Models information
         */
        $models = [];
        $modelsInstances = [];

        // Convert selected models in an array
        if (!isset($tables[0])) {
            $selectedModels = [$tables];
        } else {
            $selectedModels = $tables;
        }

        // Convert selected columns in an array
        if (!isset($columns[0])) {
            $selectColumns = [$columns];
        } else {
            $selectColumns = $columns;
        }

        $manager = $this->manager;
        $metaData = $this->metaData;

        if (!is_object($manager)) {
            throw new Exception(
                            "A models-manager is required to execute the query"
            );
        }

        if (!is_object($metaData)) {
            throw new Exception(
                            "A meta-data is required to execute the query"
            );
        }

        // Process selected models
        $number = 0;
        $automaticJoins = [];

        foreach ($selectedModels as $selectedModel) {
            $qualifiedName = $selectedModel["qualifiedName"];
            $modelName = $qualifiedName["name"];

            // Load a model instance from the models $manager
            $model = $manager->load($modelName);

            // Define a complete schema/source
            $schema = $model->getSchema();
            $source = $model->getSource();

            // Obtain the real source including the schema
            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = $source;
            }

            /**
             * If an alias is defined for a model then the model cannot be
             * referenced in the column list
             */
            $alias = $selectedModel["alias"] ?? null;
            if ($alias !== null) {

                // Check if the alias was used before
                if (isset($sqlAliases[$alias])) {
                    throw new Exception(
                                    "Alias '" . $alias . "' is used more than once, when preparing: " . $this->phql
                    );
                }

                $sqlAliases[$alias] = $alias;
                $sqlAliasesModels[$alias] = $modelName;
                $sqlModelsAliases[$modelName] = $alias;
                $sqlAliasesModelsInstances[$alias] = $model;

                /**
                 * Append or convert complete source to an array
                 */
                if (is_array($completeSource)) {
                    $completeSource[] = $alias;
                } else {
                    $completeSource = [$source, null, $alias];
                }

                $models[$modelName] = $alias;
            } else {
                $alias = $source;
                $sqlAliases[$modelName] = $source;
                $sqlAliasesModels[$modelName] = $modelName;
                $sqlModelsAliases[$modelName] = $modelName;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $models[$modelName] = $source;
            }

            // Eager load any specified relationship(s)
            $with = $selectedModel["with"] ?? null;
            if ($with !== null) {

                if (!isset($with[0])) {
                    $withs = [$with];
                } else {
                    $withs = $with;
                }

                // Simulate the definition of inner joins
                foreach ($withs as $withItem) {
                    $joinAlias = "AA" . $number;
                    $relationModel = $withItem["name"];

                    $relation = $manager->getRelationByAlias(
                            $modelName,
                            $relationModel
                    );

                    if (is_object($relation)) {
                        $bestAlias = $relation->getOption("alias");
                        $relationModel = $relation->getReferencedModel();
                        $eagerType = $relation->getType();
                    } else {
                        $relation = $manager->getRelationsBetween(
                                $modelName,
                                $relationModel
                        );

                        if (!is_object($relation)) {
                            throw new Exception(
                                            "Can't find a relationship between '" . $modelName . "' and '" . $relationModel . "' when preparing: " . $this->phql
                            );
                        }

                        $bestAlias = $relation->getOption("alias");
                        $relationModel = $relation->getReferencedModel();
                        $eagerType = $relation->getType();
                    }

                    $selectColumns[] = [
                        "type" => PHQL_T_DOMAINALL,
                        "column" => $joinAlias,
                        "eager" => $alias,
                        "eagerType" => $eagerType,
                        "balias" => $bestAlias
                    ];

                    $automaticJoins[] = [
                        "type" => PHQL_T_INNERJOIN,
                        "qualified" => [
                            "type" => PHQL_T_QUALIFIED,
                            "name" => $relationModel
                        ],
                        "alias" => [
                            "type" => PHQL_T_QUALIFIED,
                            "name" => $joinAlias
                        ]
                    ];

                    $number++;
                }
            }

            $sqlModels[] = $modelName;
            $sqlTables[] = $completeSource;
            $modelsInstances[$modelName] = $model;
        }

        // Assign Models/Tables information
        if (!$merge) {
            $this->models = $models;
            $this->modelsInstances = $modelsInstances;
            $this->sqlAliases = $sqlAliases;
            $this->sqlAliasesModels = $sqlAliasesModels;
            $this->sqlModelsAliases = $sqlModelsAliases;
            $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;
        } else {
            $tempModels = $this->models;
            $tempModelsInstances = $this->modelsInstances;
            $tempSqlAliases = $this->sqlAliases;
            $tempSqlAliasesModels = $this->sqlAliasesModels;
            $tempSqlModelsAliases = $this->sqlModelsAliases;
            $tempSqlAliasesModelsInstances = $this->sqlAliasesModelsInstances;

            $this->models = array_merge($this->models, $models);
            $this->modelsInstances = array_merge($this->modelsInstances, $modelsInstances);
            $this->sqlAliases = array_merge($this->sqlAliases, $sqlAliases);
            $this->sqlAliasesModels = array_merge($this->sqlAliasesModels, $sqlAliasesModels);
            $this->sqlModelsAliases = array_merge($this->sqlModelsAliases, $sqlModelsAliases);
            $this->sqlAliasesModelsInstances = array_merge($this->sqlAliasesModelsInstances, $sqlAliasesModelsInstances);
        }

        $joins = $select["joins"] ?? [];

        // Join existing JOINS with automatic Joins
        if (count($joins)) {
            if (count($automaticJoins)) {
                if (isset($joins[0])) {
                    $select["joins"] = array_merge($joins, $automaticJoins);
                } else {
                    $automaticJoins[] = $joins;
                    $select["joins"] = $automaticJoins;
                }
            }

            $sqlJoins = $this->_getJoins($select);
        } else {
            if (count($automaticJoins)) {
                $select["joins"] = $automaticJoins;
                $sqlJoins = $this->_getJoins($select);
            } else {
                $sqlJoins = [];
            }
        }

        // Resolve selected columns
        $position = 0;
        $sqlColumnAliases = [];

        foreach ($selectColumns as $column) {
            foreach ($this->_getSelectColumn($column) as $sqlColumn) {
                /**
                 * If "alias" is set, the user defined an alias for the column
                 */
                $alias = $column["alias"] ?? null;
                if ($alias !== null) {

                    /**
                     * The best alias is the one provided by the user
                     */
                    $sqlColumn["balias"] = $alias;
                    $sqlColumn["$sqlAlias"] = $alias;
                    $sqlColumns[$alias] = $sqlColumn;
                    $sqlColumnAliases[$alias] = true;
                } else {
                    /**
                     * "balias" is the best alias chosen for the column
                     */
                    $alias = $sqlColumn["balias"] ?? null;
                    if ($alias !== null) {

                        $sqlColumns[$alias] = $sqlColumn;
                    } else {
                        if ($sqlColumn["type"] == "scalar") {
                            $sqlColumns["_" . $position] = $sqlColumn;
                        } else {
                            $sqlColumns[] = $sqlColumn;
                        }
                    }
                }

                $position++;
            }
        }

        $this->sqlColumnAliases[$this->nestingLevel] = $sqlColumnAliases;

        // $sqlSelect is the final prepared SELECT
        $sqlSelect = [
            "models" => $sqlModels,
            "tables" => $sqlTables,
            "columns" => $sqlColumns
        ];

        $distinct = $select["distinct"] ?? null;
        if ($distinct !== null) {

            $sqlSelect["distinct"] = $distinct;
        }

        if (count($sqlJoins)) {
            $sqlSelect["joins"] = $sqlJoins;
        }

        // Process "WHERE" clause if set
        $where = $ast["where"] ?? null;
        if ($where !== null) {

            $sqlSelect["where"] = $this->_getExpression($where);
        }

        // Process "GROUP BY" clause if set
        $groupBy = $ast["groupBy"] ?? null;
        if ($groupBy !== null) {

            $sqlSelect["group"] = $this->_getGroupClause($groupBy);
        }

        // Process "HAVING" clause if set
        $having = $ast["having"] ?? null;
        if (!empty($having)) {
            $sqlSelect["having"] = $this->_getExpression($having);
        }

        // Process "ORDER BY" clause if set
        $order = $ast["orderBy"] ?? null;
        if (!empty($order)) {
            $sqlSelect["order"] = $this->_getOrderClause($order);
        }

        // Process "LIMIT" clause if set
        $limit = $ast["limit"] ?? null;
        if (!empty($limit)) {

            $sqlSelect["limit"] = $this->_getLimitClause($limit);
        }

        // Process "FOR UPDATE" clause if set
        if (isset($ast["forUpdate"])) {
            $sqlSelect["forUpdate"] = true;
        }

        if (merge) {
            $this->models = $tempModels;
            $this->modelsInstances = $tempModelsInstances;
            $this->sqlAliases = $tempSqlAliases;
            $this->sqlAliasesModels = $tempSqlAliasesModels;
            $this->sqlModelsAliases = $tempSqlModelsAliases;
            $this->sqlAliasesModelsInstances = $tempSqlAliasesModelsInstances;
        }

        $this->nestingLevel--;

        return $sqlSelect;
    }

    /**
     * Analyzes an INSERT intermediate code and produces an array to be executed
     * later
     */
    final protected function _prepareInsert(): array {
        $ast = $this->ast;

        if (!isset($ast["qualifiedName"])) {
            throw new Exception("Corrupted INSERT AST");
        }

        if (!isset($ast["values"])) {
            throw new Exception("Corrupted INSERT AST");
        }

        $qualifiedName = $ast["qualifiedName"];

        // Check if the related model exists
        if (!isset($qualifiedName["name"])) {
            throw new Exception("Corrupted INSERT AST");
        }

        $manager = $this->manager;
        $modelName = $qualifiedName["name"];

        $model = $manager->load($modelName);
        $source = $model->getSource();
        $schema = $model->getSchema();

        if ($schema) {
            $source = [$schema, $source];
        }

        $notQuoting = false;
        $exprValues = [];

        foreach ($ast["values"] as $exprValue) {
            // Resolve every $expression in the "values" clause
            $exprValues[] = [
                "type" => $exprValue["type"],
                "value" => $this->_getExpression($exprValue, $notQuoting)
            ];
        }

        $sqlInsert = [
            "model" => $modelName,
            "table" => $source
        ];

        $metaData = $this->metaData;

        $fields = $ast["fields"] ?? null;
        if ($fields !== null) {

            $sqlFields = [];

            foreach ($fields as $field) {
                $name = $field["name"];

                // Check that inserted fields are part of the model
                if (!$metaData->hasAttribute($model, $name)) {
                    throw new Exception(
                                    "The model '" . $modelName . "' doesn't have the attribute '" . $name . "', when preparing: " . $this->phql
                    );
                }

                // Add the file to the insert list
                $sqlFields[] = $name;
            }

            $sqlInsert["fields"] = $sqlFields;
        }

        $sqlInsert["values"] = $exprValues;

        return $sqlInsert;
    }

    /**
     * Analyzes an UPDATE intermediate code and produces an array to be executed
     * later
     */
    final protected function _prepareUpdate(): array {
        $ast = $this->ast;

        $update = $ast["update"] ?? null;
        if ($update === null) {

            throw new Exception("Corrupted UPDATE AST");
        }

        $tables = $update["tables"] ?? null;
        if ($tables === null) {

            throw new Exception("Corrupted UPDATE AST");
        }

        $values = $update["values"] ?? null;
        if ($values === null) {

            throw new Exception("Corrupted UPDATE AST");
        }

        /**
         * We use these arrays to store info related to models, alias and its
         * sources. With them we can rename columns later
         */
        $models = [];
        $modelsInstances = [];

        $sqlTables = [];
        $sqlModels = [];
        $sqlAliases = [];
        $sqlAliasesModelsInstances = [];

        if (!isset($tables[0])) {
            $updateTables = [$tables];
        } else {
            $updateTables = $tables;
        }

        $manager = $this->manager;

        foreach ($updateTables as $table) {
            $qualifiedName = $table["qualifiedName"];
            $modelName = $qualifiedName["name"];

            /**
             * Load a model instance from the models manager
             */
            $model = $manager->load($modelName);
            $source = $model->getSource();
            $schema = $model->getSchema();

            /**
             * Create a full source representation including schema
             */
            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = [$source, null];
            }

            /**
             * Check if the table is aliased
             */
            $alias = $table["alias"] ?? null;
            if ($alias !== null) {

                $sqlAliases[$alias] = $alias;
                $completeSource[] = $alias;
                $sqlTables[] = $completeSource;
                $sqlAliasesModelsInstances[$alias] = $model;
                $models[$alias] = $modelName;
            } else {
                $sqlAliases[$modelName] = $source;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $sqlTables[] = $source;
                $models[$modelName] = $source;
            }

            $sqlModels[] = $modelName;
            $modelsInstances[$modelName] = $model;
        }

        /**
         * Update the models/alias/sources in the object
         */
        $this->models = $models;
        $this->modelsInstances = $modelsInstances;
        $this->sqlAliases = $sqlAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;

        $sqlFields = [];
        $sqlValues = [];

        if (!isset($values[0])) {
            $updateValues = [$values];
        } else {
            $updateValues = $values;
        }

        $notQuoting = false;

        foreach ($updateValues as $updateValue) {
            $sqlFields[] = $this->_getExpression($updateValue["column"], $notQuoting);
            $exprColumn = $updateValue["expr"];
            $sqlValues[] = [
                "type" => $exprColumn["type"],
                "value" => $this->_getExpression($exprColumn, $notQuoting)
            ];
        }

        $sqlUpdate = [
            "tables" => $sqlTables,
            "models" => $sqlModels,
            "fields" => $sqlFields,
            "values" => $sqlValues
        ];

        $where = $ast["where"] ?? null;
        if ($where !== null) {

            $sqlUpdate["where"] = $this->_getExpression($where, true);
        }

        $limit = $ast["limit"] ?? null;
        if ($limit !== null) {

            $sqlUpdate["limit"] = $this->_getLimitClause($limit);
        }

        return $sqlUpdate;
    }

    /**
     * Analyzes a DELETE intermediate code and produces an array to be executed
     * later
     */
    final protected function _prepareDelete(): array {
        $ast = $this->ast;

        $delete = $ast["delete"] ?? null;
        if ($delete === null) {

            throw new Exception("Corrupted DELETE AST");
        }

        $tables = $delete["tables"] ?? null;
        if ($tables === null) {

            throw new Exception("Corrupted DELETE AST");
        }

        /**
         * We use these arrays to store info related to $models, alias and its
         * sources. Thanks to them we can rename columns later
         */
        $models = [];
        $modelsInstances = [];

        $sqlTables = [];
        $sqlModels = [];
        $sqlAliases = [];
        $sqlAliasesModelsInstances = [];

        if (!isset($tables[0])) {
            $deleteTables = [$tables];
        } else {
            $deleteTables = $tables;
        }

        $manager = $this->manager;

        foreach ($deleteTables as $table) {
            $qualifiedName = $table["qualifiedName"];
            $modelName = $qualifiedName["name"];

            /**
             * Load a model instance from the models manager
             */
            $model = $manager->load($modelName);
            $source = $model->getSource();
            $schema = $model->getSchema();

            if ($schema) {
                $completeSource = [$source, $schema];
            } else {
                $completeSource = [$source, null];
            }

            $alias = $table["alias"] ?? null;
            if ($alias !== null) {

                $sqlAliases[$alias] = $alias;
                $completeSource[] = $alias;
                $sqlTables[] = $completeSource;
                $sqlAliasesModelsInstances[$alias] = $model;
                $models[$alias] = $modelName;
            } else {
                $sqlAliases[$modelName] = $source;
                $sqlAliasesModelsInstances[$modelName] = $model;
                $sqlTables[] = $source;
                $models[$modelName] = $source;
            }

            $sqlModels[] = $modelName;
            $modelsInstances[$modelName] = $model;
        }

        /**
         * Update the models/alias/sources in the object
         */
        $this->models = $models;
        $this->modelsInstances = $modelsInstances;
        $this->sqlAliases = $sqlAliases;
        $this->sqlAliasesModelsInstances = $sqlAliasesModelsInstances;

        $sqlDelete = [];
        $sqlDelete["tables"] = $sqlTables;
        $sqlDelete["models"] = $sqlModels;

        $where = $ast["where"] ?? null;
        if ($where !== null) {

            $sqlDelete["where"] = $this->_getExpression($where, true);
        }

        $limit = $ast["limit"] ?? null;
        if ($limit !== null) {

            $sqlDelete["limit"] = $this->_getLimitClause($limit);
        }

        return $sqlDelete;
    }

    /**
     * Not relevant when generating SQL directly
     */
    public function parse(): array {

        return [];
    }

    /**
     * Returns the current cache backend instance
     */
    public function getCache(): AdapterInterface {
        return $this->cache;
    }

    /**
     * Executes the SELECT intermediate representation producing a
     * Phalcon\Mvc\Model\Resultset
     */
    final protected function _executeSelect(array $intermediate,
            array $bindParams, array $bindTypes, bool $simulate = false): ResultsetInterface|array {
        $manager = $this->manager;

        /**
         * Get a database connection
         */
        $connectionTypes = [];
        $models = $intermediate["models"];

        foreach ($models as $modelName) {
            // Load model if it is not loaded
            $model = $this->modelsInstances[$modelName] ?? null;
            if ($model === null) {

                $model = $manager->load($modelName);
                $this->modelsInstances[$modelName] = $model;
            }

            $connection = $this->getReadConnection(
                    $model,
                    $intermediate,
                    $bindParams,
                    $bindTypes
            );

            if (is_object($connection)) {
                // More than one type of connection is not allowed
                $connectionTypes[$connection->getType()] = true;

                if (count($connectionTypes) == 2) {
                    throw new Exception(
                                    "Cannot use models of different database systems in the same query"
                    );
                }
            }
        }

        $columns = $intermediate["columns"];

        $haveObjects = false;
        $haveScalars = false;
        $isComplex = false;

        // Check if the resultset have objects and how many of them have
        $numberObjects = 0;
        $columns1 = $columns;

        foreach ($columns as $column) {
            if (!is_array($column)) {
                throw new Exception("Invalid column definition");
            }

            if ($column["type"] == "scalar") {
                if (!isset($column["balias"])) {
                    $isComplex = true;
                }

                $haveScalars = true;
            } else {
                $haveObjects = true;
                $numberObjects++;
            }
        }

        // Check if the resultset to return is complex or simple
        if (!$isComplex) {
            if ($haveObjects) {
                if ($haveScalars) {
                    $isComplex = true;
                } else {
                    if ($numberObjects == 1) {
                        $isSimpleStd = false;
                    } else {
                        $isComplex = true;
                    }
                }
            } else {
                $isSimpleStd = true;
            }
        }

        // Processing selected columns
        $instance = null;
        $selectColumns = [];
        $simpleColumnMap = [];
        $metaData = $this->metaData;

        foreach ($columns as $aliasCopy => $column) {
            $sqlColumn = $column["column"];

            // Complete objects are treated in a different way
            if ($column["type"] == "object") {
                $modelName = $column["model"];

                /**
                 * Base instance
                 */
                $instance = $this->modelsInstances[$modelName] ?? null;
                if ($instance === null) {

                    $instance = $manager->load($modelName);
                    $this->modelsInstances[$modelName] = $instance;
                }

                $attributes = $metaData->getAttributes($instance);

                if ($isComplex) {
                    /**
                     * If the resultset is complex we open every model into
                     * their columns
                     */
                    if (\globals_get("orm.$column_renaming")) {
                        $columnMap = $metaData->getColumnMap($instance);
                    } else {
                        $columnMap = null;
                    }

                    // Add every attribute in the model to the generated select
                    foreach ($attributes as $attribute) {
                        $selectColumns[] = [
                            $attribute,
                            $sqlColumn,
                            "_" . $sqlColumn . "_" . $attribute
                        ];
                    }

                    /**
                     * We cache required meta-data to make its future access
                     * faster
                     */
                    $columns1[$aliasCopy]["instance"] = $instance;
                    $columns1[$aliasCopy]["attributes"] = $attributes;
                    $columns1[$aliasCopy]["columnMap"] = $columnMap;

                    // Check if the model keeps snapshots
                    $isKeepingSnapshots = (bool) $manager->isKeepingSnapshots($instance);
                    if ($isKeepingSnapshots) {
                        $columns1[$aliasCopy]["keepSnapshots"] = $isKeepingSnapshots;
                    }
                } else {
                    /**
                     * Query only the columns that are registered as attributes
                     * in the metaData
                     */
                    foreach ($attributes as $attribute) {
                        $selectColumns[] = [$attribute, $sqlColumn];
                    }
                }
            } else {
                /**
                 * Create an $alias if the column doesn't have one
                 */
                if (is_int($aliasCopy)) {
                    $columnAlias = [$sqlColumn, null];
                } else {
                    $columnAlias = [$sqlColumn, null, $aliasCopy];
                }

                $selectColumns[] = $columnAlias;
            }

            /**
             * Simulate a column map
             */
            if (!$isComplex && $isSimpleStd) {
                $sqlAlias = $column["$sqlAlias"] ?? null;
                if ($sqlAlias !== null) {

                    $simpleColumnMap[$sqlAlias] = $aliasCopy;
                } else {
                    $simpleColumnMap[$aliasCopy] = $aliasCopy;
                }
            }
        }

        $processed = [];
        $bindCounts = [];
        $intermediate["columns"] = $selectColumns;

        /**
         * Replace the placeholders
         */
        foreach ($bindParams as $wildcard => $value) {
            if (is_int($wildcard)) {
                $wildcardValue = ":" . $wildcard;
            } else {
                $wildcardValue = $wildcard;
            }

            $processed[$wildcardValue] = $value;

            if (is_array($value)) {
                $bindCounts[$wildcardValue] = count($value);
            }
        }

        $processedTypes = [];

        /**
         * Replace the bind Types
         */
        foreach ($bindTypes as $typeWildcard => $value) {
            if (is_int($typeWildcard)) {
                $processedTypes[":" . $typeWildcard] = $value;
            } else {
                $processedTypes[$typeWildcard] = $value;
            }
        }

        if (count($bindCounts)) {
            $intermediate["bindCounts"] = $bindCounts;
        }

        /**
         * The corresponding SQL dialect generates the SQL statement based
         * accordingly with the database system
         */
        $dialect = $connection->getDialect();
        $sqlSelect = $dialect->select($intermediate);

        if ($this->sharedLock) {
            $sqlSelect = $dialect->sharedLock($sqlSelect);
        }

        /**
         * Return the SQL to be executed instead of execute it
         */
        if (simulate) {
            return [
                "$sql" => $sqlSelect,
                "bind" => $processed,
                "bindTypes" => $processedTypes
            ];
        }

        /**
         * Execute the query
         */
        $result = $connection->query($sqlSelect, $processed, $processedTypes);

        /**
         * Check if the query has data
         *
         * Previous if [leaving here on purpose]:
         * if (result instanceof ResultInterface && result->numRows()) {
         */
        if ($result instanceof ResultInterface) {
            $resultData = $result;
        } else {
            $resultData = null;
        }

        /**
         * Choose a resultset type
         */
        $cache = $this->cache;

        if (!$isComplex) {
            /**
             * Select the base object
             */
            if ($isSimpleStd) {
                /**
                 * If the result is a simple standard object use an
                 * Phalcon\Mvc\Model\Row as base
                 */
                $resultObject = new Row();

                /**
                 * Standard objects can't keep snapshots
                 */
                $isKeepingSnapshots = false;
            } else {
                if (is_object($instance)) {
                    $resultObject = $instance;
                } else {
                    $resultObject = $model;
                }

                /**
                 * Get the column map
                 */
                if (!\globals_get("orm.cast_on_hydrate")) {
                    $simpleColumnMap = $metaData->getColumnMap($resultObject);
                } else {
                    $columnMap = $metaData->getColumnMap($resultObject);
                    $typesColumnMap = $metaData->getDataTypes($resultObject);

                    if ($columnMap === null) {
                        $simpleColumnMap = [];

                        foreach ($metaData->getAttributes($resultObject) as $attribute) {
                            $simpleColumnMap[$attribute] = [
                                $attribute,
                                $typesColumnMap[$attribute]
                            ];
                        }
                    } else {
                        $simpleColumnMap = [];

                        foreach ($columnMap as $column => $attribute) {
                            $simpleColumnMap[$column] = [
                                $attribute,
                                $typesColumnMap[$column]
                            ];
                        }
                    }
                }

                /**
                 * Check if the model keeps snapshots
                 */
                $isKeepingSnapshots = (bool) $manager->isKeepingSnapshots($resultObject);
            }

            if ($resultObject instanceof ModelInterface && method_exists($resultObject, "getResultsetClass")) {
                $resultsetClassName = $resultObject->getResultsetClass();

                if ($resultsetClassName) {
                    if (!class_exists($resultsetClassName)) {
                        throw new Exception(
                                        "Resultset class \"" . $resultsetClassName . "\" not found"
                        );
                    }

                    if (!is_subclass_of($resultsetClassName, "Phalcon\\Mvc\\Model\\ResultsetInterface")) {
                        throw new Exception(
                                        "Resultset class \"" . $resultsetClassName . "\" must be an implementation of Phalcon\\Mvc\\Model\\ResultsetInterface"
                        );
                    }

                    return Create::instance_params(
                                    $resultsetClassName,
                                    [
                                        $simpleColumnMap,
                                        $resultObject,
                                        $resultData,
                                        $cache,
                                        $isKeepingSnapshots
                                    ]
                    );
                }
            }

            /**
             * Simple resultsets contains only complete objects
             */
            return new Simple(
                    $simpleColumnMap,
                    $resultObject,
                    $resultData,
                    $cache,
                    $isKeepingSnapshots
            );
        }

        /**
         * Complex resultsets may contain complete objects and scalars
         */
        return new Complex(
                $columns1,
                $resultData,
                $cache
        );
    }

    /**
     * Executes the INSERT intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     */
    final protected function _executeInsert(array $intermediate,
            array $bindParams, array $bindTypes): StatusInterface {
        $modelName = $intermediate["model"];

        $manager = $this->manager;

        $model = $this->modelsInstances[$modelName] ?? null;
        if ($model === null) {

            $model = $manager->load($modelName);
        }

        $connection = $this->getWriteConnection(
                $model,
                $intermediate,
                $bindParams,
                $bindTypes
        );

        $metaData = $this->metaData;
        $attributes = $metaData->getAttributes($model);

        $automaticFields = false;

        /**
         * The "fields" index may already have the fields to be used in the
         * query
         */
        $fields = $intermediate["fields"] ?? null;
        if ($fields === null) {

            $automaticFields = true;
            $fields = $attributes;

            if (\globals_get("orm.column_renaming")) {
                $columnMap = $metaData->getColumnMap($model);
            } else {
                $columnMap = null;
            }
        }

        $values = $intermediate["values"];

        /**
         * The number of calculated values must be equal to the number of fields
         * in the model
         */
        if (count($fields) != count($values)) {
            throw new Exception(
                            "The column count does not match the values count"
            );
        }

        /**
         * Get the dialect to resolve the SQL expressions
         */
        $dialect = $connection->getDialect();

        $insertValues = [];
        foreach ($values as $number => $value) {
            $exprValue = $value["value"];

            switch ($value["type"]) {

                case PHQL_T_STRING:
                case PHQL_T_INTEGER:
                case PHQL_T_DOUBLE:
                    $insertValue = $dialect->getSqlExpression($exprValue);
                    break;

                case PHQL_T_NULL:
                    $insertValue = null;
                    break;

                case PHQL_T_NPLACEHOLDER:
                case PHQL_T_SPLACEHOLDER:
                case PHQL_T_BPLACEHOLDER:
                    $wildcard = str_replace(
                            ":",
                            "",
                            $dialect->getSqlExpression($exprValue)
                    );

                    $insertValue = $bindParams[$wildcard] ?? null;
                    if ($insertValue === null) {

                        throw new Exception(
                                        "Bound parameter '" . $wildcard . "' cannot be replaced because it isn't in the placeholders list"
                        );
                    }

                    break;

                default:
                    $insertValue = new RawValue(
                            $dialect->getSqlExpression($exprValue)
                    );

                    break;
            }

            $fieldName = $fields[$number];

            /**
             * If the user didn't define a column list we assume all the model's
             * attributes as columns
             */
            if ($automaticFields && is_array($columnMap)) {
                $attributeName = $columnMap[$fieldName] ?? null;
                if ($attributeName === null) {

                    throw new Exception(
                                    "Column '" . $fieldName . "' isn't part of the column map"
                    );
                }
            } else {
                $attributeName = $fieldName;
            }

            $insertValues[$attributeName] = $insertValue;
        }

        /**
         * Get model from the Models Manager
         */
        $insertModel = $manager->load($modelName);

        $insertModel->assign($insertValues);

        /**
         * Call 'create' to ensure that an insert is performed
         * Return the insert status
         */
        return new Status(
                $insertModel->create(),
                $insertModel
        );
    }

    /**
     * Executes the UPDATE intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     */
    final protected function _executeUpdate(array $intermediate,
            array $bindParams, array $bindTypes): StatusInterface {
        $models = $intermediate["models"];

        if (isset($models[1])) {
            throw new Exception(
                            "Updating several models at the same time is still not supported"
            );
        }

        $modelName = $models[0];

        /**
         * Load the model from the modelsManager or from the modelsInstances
         * property
         */
        $model = $this->modelsInstances[$modelName] ?? null;
        if ($model === null) {

            $model = $this->manager->load($modelName);
        }

        $connection = $this->getWriteConnection(
                $model,
                $intermediate,
                $bindParams,
                $bindTypes
        );

        $dialect = $connection->getDialect();

        $fields = $intermediate["fields"];
        $values = $intermediate["values"];

        /**
         * updateValues is applied to every record
         */
        $updateValues = [];

        /**
         * If a placeholder is unused in the update values, we assume that it's
         * used in the SELECT
         */
        $selectBindParams = $bindParams;
        $selectBindTypes = $bindTypes;

        foreach ($fields as $number => $field) {
            $value = $values[$number];
            $exprValue = $value["value"];

            if (isset($field["balias"])) {
                $fieldName = $field["balias"];
            } else {
                $fieldName = $field["name"];
            }

            switch ($value["type"]) {
                case PHQL_T_STRING:
                case PHQL_T_INTEGER:
                case PHQL_T_DOUBLE:
                    $updateValue = $dialect->getSqlExpression($exprValue);
                    break;

                case PHQL_T_NULL:
                    $updateValue = null;
                    break;

                case PHQL_T_NPLACEHOLDER:
                case PHQL_T_SPLACEHOLDER:
                case PHQL_T_BPLACEHOLDER:
                    $wildcard = str_replace(
                            ":",
                            "",
                            $dialect->getSqlExpression($exprValue)
                    );

                    $updateValue = $bindParams[$wildcard] ?? null;
                    if ($updateValue === null) {

                        throw new Exception(
                                        "Bound parameter '" . $wildcard . "' cannot be replaced because it's not in the placeholders list"
                        );
                    }

                    unset($selectBindParams[$wildcard]);
                    unset($selectBindTypes[$wildcard]);

                    break;

                case PHQL_T_BPLACEHOLDER:
                    throw new Exception("Not supported");

                default:
                    $updateValue = new RawValue(
                            $dialect->getSqlExpression($exprValue)
                    );

                    break;
            }

            $updateValues[$fieldName] = $updateValue;
        }

        /**
         * We need to query the records related to the update
         */
        $records = $this->getRelatedRecords(
                $model,
                $intermediate,
                $selectBindParams,
                $selectBindTypes
        );

        /**
         * If there are no records to apply the update we return success
         */
        if (!count($deletes)) {
            return new Status(true);
        }

        $connection = $this->getWriteConnection(
                $model,
                $intermediate,
                $bindParams,
                $bindTypes
        );

        /**
         * Create a transaction in the write connection
         */
        $connection->begin();

        $deletes->rewind();

        //for record in iterator(records) {
        while ($deletes->valid()) {
            $delete = $deletes->current();

            $delete->assign($updateValues);

            /**
             * We apply the executed values to every record found
             */
            if (!$delete->update()) {
                /**
                 * Rollback the transaction on failure
                 */
                $connection->rollback();

                return new Status(false, $delete);
            }

            $deletes->next();
        }

        /**
         * Commit transaction on success
         */
        $connection->commit();

        return new Status(true);
    }

    /**
     * Executes the DELETE intermediate representation producing a
     * Phalcon\Mvc\Model\Query\Status
     */
    final protected function _executeDelete(array $intermediate,
            array $bindParams, array $bindTypes): StatusInterface {

        $models = $intermediate["models"];

        if (isset($models[1])) {
            throw new Exception(
                            "Delete from several models at the same time is still not supported"
            );
        }

        $modelName = $models[0];

        /**
         * Load the model from the modelsManager or from the modelsInstances property
         */
        $model = $this->modelsInstances[$modelName] ?? null;
        if ($model === null) {

            $model = $this->manager->load($modelName);
        }

        /**
         * Get the records to be deleted
         */
        $deletes = $this->getRelatedRecords(
                $model,
                $intermediate,
                $bindParams,
                $bindTypes
        );

        /**
         * If there are no records to delete we return success
         */
        if (!count($deletes)) {
            return new Status(true);
        }

        $connection = $this->getWriteConnection(
                $model,
                $intermediate,
                $bindParams,
                $bindTypes
        );

        /**
         * Create a transaction in the write $connection
         */
        $connection->begin();
        $deletes->rewind();

        while ($deletes->valid()) {
            $delete = $deletes->current();

            /**
             * We delete every $delete found
             */
            if (!$delete->delete()) {
                /**
                 * Rollback the transaction
                 */
                $connection->rollback();

                return new Status(false, $delete);
            }

            $deletes->next();
        }

        /**
         * Commit the transaction
         */
        $connection->commit();

        /**
         * Create a status to report the deletion status
         */
        return new Status(true);
    }

    /**
     * Query the records on which the UPDATE/DELETE operation will be done
     *
     * @todo Remove in v5.0
     * @deprecated Use getRelatedRecords()
     *
     * @return ResultsetInterface
     */
    final protected function _getRelatedRecords(ModelInterface $model, array $intermediate,
            array $bindParams, array $bindTypes): ResultsetInterface {
        return $this->getRelatedRecords($model, $intermediate, $bindParams, $bindTypes);
    }

    /**
     * Query the records on which the UPDATE/DELETE operation will be done
     *
     * @return ResultsetInterface
     */
    final protected function getRelatedRecords(ModelInterface $model, array $intermediate,
            array $bindParams, array $bindTypes): ResultsetInterface {

        /**
         * Instead of create a PHQL string statement we manually create the IR
         * representation
         */
        $selectIr = [
            "columns" => [
                [
                    "type" => "object",
                    "model" => get_class($model),
                    "column" => $model->getSource()
                ]
            ],
            "models" => $intermediate["models"],
            "tables" => $intermediate["tables"]
        ];

        /**
         * Check if a WHERE clause was specified
         */
        $whereConditions = $intermediate["where"] ?? null;
        if ($whereConditions !== null) {

            $selectIr["where"] = $whereConditions;
        }

        /**
         * Check if a LIMIT clause was specified
         */
        $limitConditions = $intermediate["limit"] ?? null;
        if ($limitConditions !== null) {

            $selectIr["limit"] = $limitConditions;
        }

        /**
         * We create another Phalcon\Mvc\Model\Query to get the related records
         */
        $query = new self();

        $query->setDI($this->container);
        $query->setType(PHQL_T_SELECT);
        $query->setIntermediate($selectIr);

        return $query->execute($bindParams, $bindTypes);
    }

    /**
     * Executes a parsed PHQL statement
     *
     * @return mixed
     */
    public function execute(array $bindParams = [], array $bindTypes = []) {
        $uniqueRow = $this->uniqueRow;
        $cacheOptions = $this->cacheOptions;

        if ($cacheOptions !== null) {
            if (!is_array($cacheOptions)) {
                throw new Exception("Invalid caching options");
            }

            /**
             * The user must set a cache key
             */
            $key = $cacheOptions["key"] ?? null;
            if ($key === null) {

                throw new Exception(
                                "A cache key must be provided to identify the cached resultset in the cache backend"
                );
            }

            /**
             * By default use use 3600 seconds (1 hour) as cache lifetime
             */
            $lifetime = Arr::get($cacheOptions, "lifetime", 3600);
            $cacheService = Arr::get($cacheOptions, "service", "modelsCache");
            $cache = $this->container->getShared($cacheService);

            if (!is_object($cache)) {
                throw new Exception("Cache service must be an object");
            }

            $result = $cache->get($key);

            if (!empty($result)) {
                if (!is_object($result)) {
                    throw new Exception(
                                    "Cache didn't return a valid $resultset"
                    );
                }

                $result->setIsFresh(false);

                /**
                 * Check if only the first row must be returned
                 */
                if ($uniqueRow) {
                    $preparedResult = $result->getFirst();
                } else {
                    $preparedResult = $result;
                }

                return $preparedResult;
            }

            $this->cache = $cache;
        }

        /**
         * The statement is parsed from its PHQL string or a previously
         * processed IR
         */
        $intermediate = $this->parse();

        /**
         * Check for default bind parameters and merge them with the passed ones
         */
        $defaultBindParams = $this->bindParams;

        if (is_array($defaultBindParams)) {
            $mergedParams = $defaultBindParams + $bindParams;
        } else {
            $mergedParams = $bindParams;
        }

        /**
         * Check for default bind types and merge them with the passed ones
         */
        $defaultBindTypes = $this->bindTypes;

        if (is_array($defaultBindTypes)) {
            $mergedTypes = $defaultBindTypes + $bindTypes;
        } else {
            $mergedTypes = $bindTypes;
        }

        $type = $this->type;

        switch ($type) {
            case PHQL_T_SELECT:
                $result = $this->_executeSelect(
                        $intermediate,
                        $mergedParams,
                        $mergedTypes
                );

                break;

            case PHQL_T_INSERT:
                $result = $this->_executeInsert(
                        $intermediate,
                        $mergedParams,
                        $mergedTypes
                );

                break;

            case PHQL_T_UPDATE:
                $result = $this->_executeUpdate(
                        $intermediate,
                        $mergedParams,
                        $mergedTypes
                );

                break;

            case PHQL_T_DELETE:
                $result = $this->_executeDelete(
                        $intermediate,
                        $mergedParams,
                        $mergedTypes
                );

                break;

            default:
                throw new Exception("Unknown statement " . $type);
        }

        /**
         * We store the resultset in the cache if any
         */
        if ($cacheOptions !== null) {
            /**
             * Only PHQL SELECTs can be cached
             */
            if (type != PHQL_T_SELECT) {
                throw new Exception(
                                "Only PHQL statements that return resultsets can be cached"
                );
            }

            $cache->set($key, $result, $lifetime);
        }

        /**
         * Check if only the first row must be returned
         */
        if ($uniqueRow) {
            $preparedResult = $result->getFirst();
        } else {
            $preparedResult = $result;
        }

        return $preparedResult;
    }

    /**
     * Executes the query returning the first result
     */
    public function getSingleResult(array $bindParams = [], array $bindTypes = []): ModelInterface {
        /**
         * The query is already programmed to return just one row
         */
        if ($this->uniqueRow) {
            return $this->execute($bindParams, $bindTypes);
        }

        return $this->execute($bindParams, $bindTypes)->getFirst();
    }

    /**
     * Sets the type of PHQL statement to be executed
     */
    public function setType(int $type): QueryInterface {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the type of PHQL statement executed
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * Set default bind parameters
     */
    public function setBindParams(array $bindParams, bool $merge = false): QueryInterface {

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
     * Returns default bind params
     */
    public function getBindParams(): array {
        return $this->bindParams;
    }

    /**
     * Set default bind parameters
     */
    public function setBindTypes(array $bindTypes, bool $merge = false): QueryInterface {

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
     * Set SHARED LOCK clause
     */
    public function setSharedLock(bool $sharedLock = false): QueryInterface {
        $this->sharedLock = $sharedLock;

        return $this;
    }

    /**
     * Returns default bind types
     */
    public function getBindTypes(): array {
        return $this->bindTypes;
    }

    /**
     * Allows to set the IR to be executed
     */
    public function setIntermediate(array $intermediate): QueryInterface {
        $this->intermediate = $intermediate;

        return $this;
    }

    /**
     * Returns the intermediate representation of the PHQL statement
     */
    public function getIntermediate(): array {
        return $this->intermediate;
    }

    /**
     * Sets the cache parameters of the query
     */
    public function cache(array $cacheOptions): QueryInterface {
        $this->cacheOptions = $cacheOptions;

        return $this;
    }

    /**
     * Returns the current cache options
     */
    public function getCacheOptions(): array {
        return $this->cacheOptions;
    }

    /**
     * Returns the SQL to be generated by the internal PHQL (only works in
     * SELECT statements)
     */
    public function getSql(): array {
        /**
         * The statement is parsed from its PHQL string or a previously
         * processed IR
         */
        $intermediate = $this->parse();

        if ($this->type == PHQL_T_SELECT) {
            return $this->_executeSelect(
                            $intermediate,
                            $this->bindParams,
                            $this->bindTypes,
                            true
            );
        }

        throw new Exception(
                        "This type of statement generates multiple SQL statements"
        );
    }

    /**
     * Destroys the internal PHQL cache
     */
    public static function clean(): void {
        self::$_irPhqlCache = [];
    }

    /**
     * Gets the read connection from the model if there is no transaction set
     * inside the query object
     */
    protected function getReadConnection(ModelInterface $model, array $intermediate = null,
            array $bindParams = [], array $bindTypes = []): AdapterInterface {

        $transaction = $this->_transaction;

        if (is_object($transaction) && $transaction instanceof TransactionInterface) {
            return $transaction->getConnection();
        }

        if (method_exists($model, "selectReadConnection")) {
            // use selectReadConnection() if implemented in extended Model class
            $connection = $model->selectReadConnection(
                    $intermediate,
                    $bindParams,
                    $bindTypes
            );

            if (!is_object($connection)) {
                throw new Exception(
                                "selectReadConnection did not return a connection"
                );
            }

            return $connection;
        }

        return $model->getReadConnection();
    }

    /**
     * Gets the write connection from the model if there is no transaction
     * inside the query object
     */
    protected function getWriteConnection(ModelInterface $model, array $intermediate = null, array $bindParams = [], array $bindTypes = []): AdapterInterface {
        $connection = null;

        $transaction = $this->_transaction;

        if (is_object($transaction) && $transaction instanceof TransactionInterface) {
            return $transaction->getConnection();
        }

        if (method_exists($model, "selectWriteConnection")) {
            $connection = $model->selectWriteConnection(
                    $intermediate,
                    $bindParams,
                    $bindTypes
            );

            if (!is_object($connection)) {
                throw new Exception(
                                "selectWriteConnection did not return a connection"
                );
            }

            return $connection;
        }
        return $model->getWriteConnection();
    }

    /**
     * allows to wrap a transaction around all queries
     */
    public function setTransaction(TransactionInterface $transaction): QueryInterface {
        $this->_transaction = $transaction;

        return $this;
    }

}
