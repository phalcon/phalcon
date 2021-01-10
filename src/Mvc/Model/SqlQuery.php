<?php

namespace Phiz\Mvc\Model;

use Phiz\Di\DiInterface;
use Phiz\Di\InjectionAwareInterface;
use Phiz\Db\{ResultInterface, Enum, Column};

use Phiz\Db\Result\Pdo as PdoResult;
use Phiz\Mvc\Model\QueryInterface;
use Phiz\Mvc\ModelInterface;

use Phiz\Mvc\Model\Resultset\Simple;
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
    protected $db = null; // may be set to Database object
    protected ?ModelInterface $instance = null;
    protected ?pdoStatement $statement = null;

    //put your code here
    public function __construct(?string $sql = null, ?DiInterface $container = null, array $options = [])
    {

        $this->sql = $sql;

        if (is_object($container)) {
            $this->setDI($container);
        }
    }
    
    /** make a Db ResultInterface appropriate to this */
    
    public function resultInterface() : ?ResultInterface
    {
        return new PdoResult($this->db, $this->pdoStatement,
                $this->sql, $this->bindParams, $this->bindTypes);
    }
    
    public function getStatement() : ?PDOStatement
    {
        return $this->pdoStatement;
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
    
    public function fetchAll(int $mode = \PDO::FETCH_ASSOC)  {
        return $this->pdoStatement->fetchAll($mode);
    }
    public function fetchOne(int $mode = \PDO::FETCH_ASSOC)  {
        return $this->pdoStatement->fetch($mode);
    }
       /**
     * Executes a parsed SQL statement
     * This overrides internally set bindParams and bindTypes.
     * @returns the PDO statement after execute call
     */
    public function execute(array $bindParams = [], array $bindTypes = []) : bool
    {
        $db = $this->container->get('db'); 
        $this->db = $db;
        $pdo = $db->getInternalHandler();
        $statement = $pdo->prepare($this->sql);
        if (empty($bindParams)) {
            $bindParams = $this->bindParams;
            $bindTypes = $this->bindTypes;
        }
        if (is_object($statement)) {
            foreach($bindParams as $bkey => $bvalue) {
                $btype = $bindTypes[$bkey];
                $statement->bindValue($bkey, $bvalue, $btype);
            }
            // now an execution, but no fetch yet.
            /// AdapterInterface $connection, \PDOStatement $result,
    /// ?string $sqlStatement = null, ?array $bindParams = null, ?array $bindTypes = null)
            $pok = $statement->execute();
            $this->pdoStatement = $statement;
            if ($pok) { 
                return true;
            }
            // TODO: get errorCode, errorInfo?
        }
        return false;  
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
        return [];
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
