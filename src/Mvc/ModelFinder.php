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

    protected array $arguments;
    protected ?string $modelName;
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

    /**
     * Called directly from static Model::findFirst
     * One argument of a variety of parameter types. 
     * Return one model instance or null
     */
    public function findFirst (string $modelName, mixed $arguments = null) : ModelInterface | null
    {
        // generate "conditions", "bind" and "bindTypes" parameters for query call
        $this->modelName = $modelName;
        
        $params = null;
        if (is_string($arguments)) {
            // If string, to be direct SQL injection, pre-bound
            $params["conditions"] = $arguments; 
        }
        else if (is_array($arguments)){
            // pre-cooked configuration
            $params = $arguments; 
        }
        else if ($arguments === null) {
            $params = [];
        }
        else{
            throw new Exception(
                "arguments passed must be of type array, string, numeric or null"
            );
        }

        $query = $this->getPreparedQuery($params, 1);

        /**
         * Return only the first row
         */
        $query->setUniqueRow(true);

        /**
         * Execute the query passing the bind-params and casting-types
         */
        $qresult = $query->execute();
        
        if (!empty($qresult)) {
            // expect array of values
            $result = Create::instance($modelName);
            $propMap = $result->columnMap();
            $result->assign($qresult, null, $propMap);
            return $result;
        }
        return null;
    }
        
    public function dispatch(string $modelName, string $method, array $arguments): null | array | ModelInterface
    {
        $this->arguments = $arguments;
        $this->modelName = $modelName;

        $attrName = null;
        /**
         * Check if the method starts with "findFirst"
         */
        if (str_starts_with($method, "findFirstBy")) {
            $attrName = substr($method, 11);
            return $this->findFirstBy($attrName);
        }

        /**
         * Check if the method starts with "find"
         */ elseif (str_starts_with($method, "findBy")) {
            $type = "findBy";
            $attrName = substr($method, 6);
        }

        /**
         * Check if the $method starts with "count"
         */ elseif (str_starts_with($method, "countBy")) {
            $type = "countBy";
            $attrName = substr($method, 7);
        }
        // $attrName must resolve to a field
        if (!$attrName) {
            return false;
        }

        /**
         * Execute the query
         */
        return $this->$type($params);
    }


    /** Return just one or null, after resolving to one set of parameters
     */
    private function findFirstBy(string $attrName): ?ModelInterface {

        $model = Create::instance($this->modelName);


        $metaData = $model->getModelsMetaData();
        //$this->metaData = $metaData;

        $propMap = $model->columnMap();
        //$this->propMap = $propMap;
        
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
        $arguments = $this->arguments;
        $value = $arguments[0] ?? null;
        $colBindTypes = $metaData->getBindTypes($model);
        
        $colType = $colBindTypes[$field];
        
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

        $query = $this->getPreparedQuery($params, 1);

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
            $result = $model;
            $result->assign($qresult, null, $propMap);
            return $result;
        }
        return null;
    }
    
    public function find(string $modelName, mixed $parameters = null) : ?ResultsetInterface
    {
        if (!is_array($parameters)) {
            $params = [];
            if ($parameters !== null) {
                $params[] = $parameters;
            }
        } else {
            $params = $parameters;
        }

        $query = $this->getPreparedQuery($params);

        /**
         * Execute the query passing the bind-params and casting-types
         */
        $resultset = $query->execute();

        /**
         * Define an hydration mode
         */
        if (is_object($resultset)) {
            $hydration = $params["hydration"] ?? null;
		if ($hydration !== null) {
                $resultset->setHydrateMode($hydration);
            }
        }

        return $resultset;
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
