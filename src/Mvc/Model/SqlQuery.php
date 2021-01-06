<?php

namespace Phalcon\Mvc\Model;

use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Db\Enum;
use Phalcon\Mvc\Model\QueryInterface;
/**
 * Not sure where this fits.
 * 
 * @author michael
 */
class SqlQuery implements QueryInterface, InjectionAwareInterface {
    
    protected ?string $sql = null;
    
    protected ?array $bindParams = [];
    
    protected ?array $bindTypes = [];
    
    protected ?array $cacheOptions = null;
    protected bool $sharedLock = false;
    protected bool $uniqueRow = false;
    
    protected ?DiInterface $container = null;
    
    protected ?ManagerInterface $manager = null;
    protected ?MetaDataInterface $metaData = null;
    
    protected $_transaction = null;     
    
    protected ?ModelInterface $instance = null;


    //put your code here
    public function __construct(?string $sql = null, ?DiInterface $container = null, array $options = [])
    {

        $this->sql = $sql;

        if (is_object($container)) {
            $this->setDI($container);
        }
    }
    
    public function setDI(DiInterface $container) : void
    {

        $manager = $container->getShared("modelsManager");

        if ( !is_object($manager)) {
            throw new Exception("Injected service 'modelsManager' is invalid");
        }

            $metaData = $container->getShared("modelsMetadata");

        if ( !is_object($metaData)) {
            throw new Exception("Injected service 'modelsMetaData' is invalid");
        }

            $this->manager = $manager;
            $this->metaData = $metaData;

            $this->container = $container;
    }

    /**
     * Returns the dependency injection container
     */
    public function getDI() : DiInterface
    {
        return $this->container;
    }

        /**
     * Set default bind parameters
     */
    public function setBindTypes(array $bindTypes, bool $merge = false) : QueryInterface
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
    
    public function setBindParams(array $bindParams, bool $merge = false) : QueryInterface
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
     * Set SHARED LOCK clause
     */
    public function setSharedLock(bool $sharedLock = false) : QueryInterface
    {
            $this->sharedLock = $sharedLock;

        return $this;
    }
    /**
     * Tells to the query if only the first row in the resultset must be
     * returned
     */
    public function setUniqueRow(bool $uniqueRow) : QueryInterface
    {
        $this->uniqueRow = $uniqueRow;

        return $this;
    }

    /**
     * Check if the query is programmed to get only the first row in the
     * resultset
     */
    public function getUniqueRow() : bool
    {
        return $this->uniqueRow;
    }
    
       /**
     * Executes a parsed PHQL statement
     *
     * @return mixed
     */
    public function execute(array $bindParams = [], array $bindTypes = []) {
        $uniqueRow = $this->uniqueRow;
        $cacheOptions = $this->cacheOptions;
        $db = $this->container->get('db'); 
        if ($uniqueRow) { 
            return $db->fetchOne($this->sql, Enum::FETCH_ASSOC, $this->bindParams, $this->bindTypes);
        }
        else {
            return $db->fetchAll($this->sql, Enum::FETCH_ASSOC, $this->bindParams, $this->bindTypes);
        }
        return null;
    }
    
    /** return {get} of Query transaction */
    public function getTransaction(): object {
        return $this->_transaction;
    }
        
    /** return {get} of Query transaction */
    public function getSql(): array {
        return [$this->sql];
    }
    /**
     * Executes the query returning the first result
     */
    public function getSingleResult(array $bindParams = [], array $bindTypes = []) : ?ModelInterface
    {
        return $this->instance;
    }
    
    
    /**
     * Sets the cache parameters of the query
     */
    public function cache(array $cacheOptions): QueryInterface {
        $this->cacheOptions = $cacheOptions;

        return $this;
    }
    
    public function parse(): array {

        $intermediate = $this->intermediate;

        if (is_array($intermediate)) {
            return $intermediate;
        }

        /**
         * This function parses the PHQL statement
         */
        $phql = $this->phql;
        $ast = Lang::parsePHQL($phql);

        $irPhql = null;
        $uniqueId = null;

        if (is_array($ast)) {
            /**
             * Check if the prepared PHQL is already cached
             * Parsed ASTs have a unique id
             */
            $uniqueId = $ast["id"] ?? null;
            if ($uniqueId !== null) {
                $irPhq = self::$_irPhqlCache[$uniqueId] ?? null;
                if (is_array($irPhql)) {
                    // Assign the type to the query
                    $this->type = $ast["type"];
                    return $irPhql;
                }
            }

            /**
             * A valid AST must have a type
             */
            $type = $ast["type"] ?? null;
            if ($type !== null) {
                $this->ast = $ast;
                $this->type = $type;

                switch ($type) {
                    case PHQL_T_SELECT:
                        $irPhql = $this->_prepareSelect();
                        break;

                    case PHQL_T_INSERT:
                        $irPhql = $this->_prepareInsert();
                        break;

                    case PHQL_T_UPDATE:
                        $irPhql = $this->_prepareUpdate();
                        break;

                    case PHQL_T_DELETE:
                        $irPhql = $this->_prepareDelete();
                        break;

                    default:
                        throw new Exception(
                                        "Unknown statement " . $type . ", when preparing: " . $phql
                        );
                }
            }
        }

        if (!is_array($irPhql)) {
            throw new Exception("Corrupted AST");
        }

        /**
         * Store the prepared AST in the cache
         */
        if (is_int($uniqueId)) {
            self::$_irPhqlCache[$uniqueId] = $irPhql;
        }

        $this->intermediate = $irPhql;

        return $irPhql;
    }
    public function getCacheOptions() : array
    {
        return $this->cacheOptions;
    }
    
    public function getBindParams() : array
    {
        return $this->bindParams;
    }
    public function getBindTypes() : array
    {
        return $this->bindTypes;
    }
}
