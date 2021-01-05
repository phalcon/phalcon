<?php

namespace Phalcon\Mvc;

use Phalcon\Di\{
    Di,
    DiInterface,
    InjectionAwareInterface
};
use Phalcon\Mvc\Model\{
    MetaDataInterface,
    QueryInterface
};
use Phalcon\Support\Str\Uncamelize;
use Phalcon\Reflect\Create;
use Phalcon\Db\Column;

/**
  A "models-finder" service.

  functor Helper to expedite process of static "FindBy" calls of a model
  This is a long involved bureacratic object framework process and deserves,
  and needs a bureacratic helper from start to finish.


 */
class ModelFinder implements ModelFinderInterface, InjectionAwareInterface {

    protected string $method;
    protected array $arguments;
    protected ?string $modelName;
    protected ?ModelInterface $model = null;
    protected ?ModelManager $manager = null;
    protected ?string $type;
    /** storage field name to object property name */
    protected ?array $propMap = null;
    protected ?array $propTypes = null;
    
    protected ?MetaDataInterface $metaData = null;
    protected ?DiInterface $container = null;

    public function __construct(?DiInterface $container = null) {
        if (is_object($container)) {
            $this->setDI($container);
        }
    }

    public function setDI(DiInterface $container): void {

        $this->container = $container;
    }

    /**
     * Returns the dependency injection container
     */
    public function getDI(): DiInterface {
        $result = $this->container;
        if ($result === null) {
            $result = Di::getDefault();
            $this->container = $result;
        }
        return $result;
    }

    public function find(string $modelName, string $method, array $arguments): ?ModelInterface {
        $this->method = $method;
        $this->arguments = $arguments;
        $this->modelName = $modelName;

        $attrName = null;
        $method = $this->method;
        /**
         * Check if the method starts with "findFirst"
         */
        if (str_starts_with($method, "findFirstBy")) {
            $type = "findFirst";
            $attrName = substr($method, 11);
        }

        /**
         * Check if the method starts with "find"
         */ elseif (starts_with($method, "findBy")) {
            $type = "find";
            $attrName = substr($method, 6);
        }

        /**
         * Check if the $method starts with "count"
         */ elseif (str_starts_with($method, "countBy")) {
            $type = "count";
            $attrName = substr($method, 7);
        }
        // $attrName must resolve to a field
        if (!$attrName) {
            return false;
        }

        $model = Create::instance($modelName);
        // TODO: also set a model manager instance?
        $this->model = $model; // save for later


        $metaData = $model->getModelsMetaData();
        $this->metaData = $metaData;

        $propMap = $model->columnMap();
        $this->propMap = $propMap;
        
        $propTypes = $metaData->getDataTypes($model);
        
        if (empty($propMap)) {
            // getReverseColumnMap doesn't seem to work, returns null
            $propMap = $metaData->getReverseColumnMap($model);
        }
        if (isset($propMap[$attrName])) {
            $field = $attrName;
        } else {
            $lcfield = lcfirst($attrName);
            $field = Uncamelize::fn($attrName);
            if (!isset($propMap[$field])) {
                throw new Exception(
                                "Cannot resolve attribute '" . $attrName . "' in the model"
                );
            }
        }

        $value = $arguments[0] ?? null;
        $colType = $propTypes[$field];

        if ($value !== null) {
            $params = [
                "conditions" => "$field  = :FP0",
                "bind" => [":FP0" => $value],
                "bindTypes" => [":FP0" => $colType]
            ];
        } else {
            $params = [
                "conditions" => $field . " IS NULL"
            ];
        }

        /**
         * Just in case remove 'conditions' and 'bind'
         */
        unset($arguments[0]);
        unset($arguments["conditions"]);
        unset($arguments["bind"]);

        $params = array_merge($params, $arguments);

        /**
         * Execute the query
         */
        return $this->$type($params);
    }

    /** Return just one or null
     */
    protected function findFirst(array $parameters): ?ModelInterface {

        if (null === $parameters) {
            $params = [];
        } elseif (is_array($parameters)) {
            $params = $parameters;
        } elseif (is_string($parameters) || is_numeric($parameters)) {
            $params = [$parameters];
        } else {
            throw new Exception(
                            "Parameters passed must be of type array, string, numeric or null"
            );
        }

        $query = static::getPreparedQuery($params, 1);

        /**
         * Return only the first row
         */
        $query->setUniqueRow(true);

        /**
         * Execute the query passing the bind-params and casting-types
         */
        $qresult = $query->execute(); //mixed result
        if (!empty($qresult)) {
            // expect array of values
            $result = $this->model;
            $result->assign($qresult, null, $this->propMap);
            return $result;
        }
        return null;
    }

    /**
     * Previously static method of Phalcon\Mvc\Model
     * shared prepare query logic for find and findFirst method
     */
    private function getPreparedQuery($params, $limit = null): QueryInterface {
        $container = $this->getDI();
        $manager = $container->getShared("modelsManager");

        /**
         * Builds a query with the passed parameters
         */
        $builder = $manager->createBuilder($params);

        $builder->from(
                $this->modelName
        );

        if ($limit != null) {
            $builder->limit($limit);
        }

        $query = $builder->getQuery();

        /**
         * Check for bind parameters
         */
        $bindParams = $params["bind"] ?? null;
        if ($bindParams !== null) {
            if (is_array($bindParams)) {
                $query->setBindParams($bindParams, true);
            }

            $bindTypes = $params["bindTypes"] ?? null;
            if ($bindTypes !== null) {
                if (is_array($bindTypes)) {
                    $query->setBindTypes($bindTypes, true);
                }
            }
        }

        $transaction = $params['transaction'] ?? null;
        if ($transaction !== null) {
            if ($transaction instanceof TransactionInterface) {
                $query->setTransaction($transaction);
            }
        }

        /**
         * Pass the cache options to the query
         */
        $cache = $params["cache"] ?? null;
        if ($cache !== null) {
            $query->cache($cache);
        }

        return $query;
    }

}
