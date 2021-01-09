<?php

namespace Phalcon\Mvc;

use Phalcon\Di\{
    Di,
    DiInterface,
    InjectionAwareInterface
};
use Phalcon\Mvc\Model\{
    MetaDataInterface,
    QueryInterface,
    Exception
};
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Support\Str\Uncamelize;
use Phalcon\Reflect\Create;
use Phalcon\Db\Column;

/**
 * A "models-finder" service.
 *
 * This is an injectable Helper class to expedite process of static "FindBy" calls 
 * from a model class. 
 * This is a long involved bureacratic object framework process and deserves,
 * and needs a bureacratic helper from start to finish.
 * 
 */
class ModelFinder implements ModelFinderInterface, InjectionAwareInterface {

    protected mixed $arguments; // passed to dispatch
    protected ?string $modelName;
    protected ?ModelInterface $model = null;
    protected ?MetaDataInterface $metaData = null;
    protected $bindTypes = null;
    
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

    private function getBindTypes() : array {
        return $this->bindTypes;
    }
    
    private function getPropTypes() : array {
        return  $this->metaData->getDataTypes($this->model);
    }
    
    private function init(string $modelName)
    {
        $this->modelName = $modelName;
        $model = Create::instance($this->modelName);
        $this->model = $model;
        $metaData = $model->getModelsMetaData();
        //debugLine("MetaData " . gettype($metaData) . " " . get_class($metaData));
        $this->metaData = $metaData;
        $this->bindTypes = $metaData->getBindTypes($model);
        
        debugLine("Table = " . $model->getSource());
        if (method_exists($model,'columnMap')) {
            $this->propMap = $model->columnMap();
        }
        else {
             // assume best case
             $attr = $metaData->getAttributes($model);
             $map = [];
             foreach($attr as $name) {
                 $map[$name] = $name;
             }
             $this->propMap = $map; 
        }
    }
    /**
     * Called directly from static Model::findFirst
     * One argument of a variety of parameter types. 
     * Return one model instance or null
     */
    public function findFirst (string $modelName, mixed $arguments = null) : ModelInterface | null
    {
        
        $this->init($modelName);
        
        //$this->propMap = $propMap;
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
        $ok = $query->execute();
        
        
        if ($ok) {
            // expect array of values
            $result = Create::instance($modelName);
            //$propMap = $result->columnMap();
            $row = $query->fetchOne();
            if ($row) {
                $result->assign($row, null);        
                return $result;
            }
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
            $this->init($modelName);
            return $this->findFirstBy(substr($method, 11));
        }

        /**
         * Check if the method starts with "find"
         */ elseif (str_starts_with($method, "findBy")) {
             $this->init($modelName);
            return $this->findBy(substr($method, 6));
        }

        /**
         * Check if the $method starts with "count"
         */ elseif (str_starts_with($method, "countBy")) {
            $type = "countBy";
            $attrName = substr($method, 7);
        }
        // $attrName must resolve to a field
        if (!$attrName) {
            throw new Exception("ModelFinder dispatch does not support $method()");
        }

        /**
         * Execute the query
         */
        return $this->$type($params);
    }

    private function findBy(string $attrName) : ?ResultsetInterface 
    {
         $propMap = $this->propMap;
        if (isset($propMap[$attrName])) {
            $field = $attrName;
        } else {
            $lcfield = lcfirst($attrName);
            $field = Uncamelize::fn($attrName);
            if (!isset($propMap[$field])) {
                throw new Exception(
                                "Cannot resolve attribute '" . $attrName . "' in the model"
                                    . PHP_EOL . print_r($propMap, true)
                );
            }
        }
        
        $arguments = $this->arguments;
        $value = $arguments[0] ?? null;
        $colBindTypes = $this->getBindTypes();
        
        debugLine("bindTypes are : " . typeof($colBindTypes));
        
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

        $ok = $query->execute(); //mixed result
        $propMap = $this->propMap;
        //$columnMap,$model,$result,AdapterInterface $cache = null,bool $keepSnapshots = null
        if ($ok) {
            $simple = new Simple($propMap, $this->model, $query->resultInterface());
            return $simple;
        }
        else {
            return null;
        }
    }
    /** Return just one or null, after resolving to one set of parameters
     */
    private function findFirstBy(string $attrName): ?ModelInterface 
    {  
        $propMap = $this->propMap;
        $arguments = $this->arguments;
        debugLine("FindFirstBy $attrName " . print_r($arguments, true) . PHP_EOL . print_r($propMap,true));
        if (isset($propMap[$attrName])) {
            $field = $attrName;
        } else {
            $lcfield = lcfirst($attrName);
            $field = Uncamelize::fn($attrName);
            if (!isset($propMap[$field])) {
                throw new Exception(
                                "Cannot resolve attribute '" . $attrName . "' in the model"
                                    . PHP_EOL . print_r($propMap, true)
                );
            }
        }
        debugLine("Resolved to $field");
        
        $value = $arguments[0] ?? null;
        
        $colBindTypes = $this->getBindTypes();
        debugLine("Bind types: " . print_f($colBindTypes,true));
        debugLine("Field: $field " . "Value: $value");
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
        debugLine("Params " . print_r($params,true));
        /**
         * Just in case remove 'conditions' and 'bind'
         */
        unset($arguments[0]);
        unset($arguments["conditions"]);
        unset($arguments["bind"]);

        $params = array_merge($params, $arguments);
        
        debugLine("Params: " . print_r($params, true));
        
        $query = $this->getPreparedQuery($params, 1);

        /**
         * Return only the first row
         */
        $query->setUniqueRow(true);

        /**
         * Execute the query passing the bind-params and casting-types
         */
      
        $ok = $query->execute(); //mixed result
        
        debugLine("Query ok: " . $ok);
        if ($ok) {
            // expect 
            $qrow = $query->fetchOne();
            $result = $this->model;
            $result->assign($qrow, null, $propMap);
            return $result;
        }
        return null;
    }
    
    /** interface expectations of this and actual method horrible */
    
    public function find(string $modelName, mixed $parameters = null) : ?ResultSetInterface
    {
        $this->init($modelName);
        
        if (!is_array($parameters)) {
            $params = [];
            if ($parameters !== null) {
                $params[] = $parameters;
            }
        } else {
            $params = $parameters;
        }

        $query = $this->getPreparedQuery($params);
        $ok = $query->execute();

        $propMap = $this->propMap;
        //$columnMap,$model,$result,AdapterInterface $cache = null,bool $keepSnapshots = null
        if ($ok) {
            $simple = new Simple($propMap, $this->model, $query->resultInterface());
            return $simple;
        }
        else {
            return null;
        }
    }
    /**
     * Previously static method of Phalcon\Mvc\Model
     * shared prepare query logic for find and findFirst method
     */
    private function getPreparedQuery($params, $limit = null): QueryInterface {
        $container = $this->getDI();
        $manager = $container->getShared("modelsManager");
        debugLine("getPreparedQuery");
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
