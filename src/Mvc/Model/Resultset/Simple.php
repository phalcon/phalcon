<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\Model\Resultset;

use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Row;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Storage\Serializer\SerializerInterface;

/**
 * Phalcon\Mvc\Model\Resultset\Simple
 *
 * Simple resultsets only contains a complete objects
 * This class builds every complete object as it is required
 */
class Simple extends Resultset
{
    protected $columnMap;
    protected $model;
    /**
     * @var bool
     */
    protected $keepSnapshots = false;

    /**
     * Phalcon\Mvc\Model\Resultset\Simple constructor
     *
     * @param array                                             columnMap
     * @param \Phalcon\Mvc\ModelInterface|Phalcon\Mvc\Model\Row model
     */
    public function __construct(
        $columnMap,
        $model,
        $result,
        AdapterInterface $cache = null,
        bool $keepSnapshots = null
    )
    {
        $this->model     = $model;
        $this->columnMap = $columnMap;
        /**
         * Set if the returned resultset must keep the record snapshots
         */
        $this->keepSnapshots = $keepSnapshots;

        parent::__construct($result, $cache);
    }

    /**
     * Returns current row in the resultset
     */
    final public function current() : ModelInterface | null
    {
        

        $activeRow = $this->activeRow;

        if($activeRow !== null) {
            return $activeRow;
        }

        /**
         * Current row is set by seek() operations
         */
        $row = $this->row;

        /**
         * Valid records are arrays
         */
        if(!is_array($row)) {
            $this->activeRow = false;

            return null;
        }

        /**
         * Get current hydration mode
         */
        $hydrateMode = $this->hydrateMode;

        /**
         * Get the resultset column map
         */
        $columnMap = $this->columnMap;

        /**
         * Hydrate based on the current hydration
         */
        switch($hydrateMode) {
            case Resultset::HYDRATE_RECORDS:
                /**
                 * Set records as dirty state PERSISTENT by default
                 * Performs the standard hydration based on objects
                 */
                if(globals_get("orm.late_state_binding")) {
                    if($this->model instanceof Model) {
                        $modelName = get_class($this->model);
                    } else {
                        $modelName = "Phalcon\\Mvc\\Model";
                    }

                    $activeRow = $modelName::cloneResultMap(
                        $this->model,
                        $row,
                        $columnMap,
                        Model::DIRTY_STATE_PERSISTENT,
                        $this->keepSnapshots
                    );
                } else {
                    $activeRow = Model::cloneResultMap(
                        $this->model,
                        $row,
                        $columnMap,
                        Model::DIRTY_STATE_PERSISTENT,
                        $this->keepSnapshots
                    );
                }

                break;

            default:
                /**
                 * Other kinds of hydrations
                 */
                $activeRow = Model::cloneResultMapHydrate(
                    $row,
                    $columnMap,
                    $hydrateMode
                );

                break;
        }

        $this->activeRow = $activeRow;

        return $activeRow;
    }

    /**
     * Returns a complete resultset as an array, if the resultset has a big
     * number of rows it could consume more memory than currently it does.
     * Export the resultset to an array couldn't be faster with a large number
     * of records
     */
    public function toArray(bool $renameColumns = true) : array
    {
        
        

        /**
         * If _rows is not present, fetchAll from database
         * and keep them in memory for further operations
         */
        $records = $this->rows;

        if(!is_array($records)) {
            $result = $this->result;

            if($this->row !== null) {
                // re-execute query if required and fetchAll rows
                $result->execute();
            }

            $records = $result->fetchAll();

            $this->row = null;
            $this->rows = $records; // keep result-set in memory
        }

        /**
         * We need to rename the whole set here, this could be slow
         *
         * Only rename when it is Model
         */
        if($renameColumns && !($this->model instanceof Row)) {
            /**
             * Get the resultset column map
             */
            $columnMap = $this->columnMap;

            if(!is_array($columnMap)) {
                return $records;
            }

            $renamedRecords = [];

            if(is_array($records)) {
                foreach ($records as $record)  {
                    $renamed = [];

                    foreach($record as $key => $value) {
                        /**
                         * Check if the key is part of the column map
                         */
                        $renamedKey = $columnMap[$key] ?? null;
                        if($renamedKey === null) {
                            throw new Exception(
                                "Column '" . $key . "' is not part of the column map"
                            );
                        }

                        if(is_array($renamedKey)) {
                            $renamedKey = $renamedKey[0] ?? null;
                            if($renamedKey === null) {
                                throw new Exception(
                                    "Column '" . $key . "' is not part of the column map"
                                );
                            }
                        }

                        $renamed[$renamedKey] = $value;
                    }

                    /**
                     * Append the renamed records to the main array
                     */
                    $renamedRecords[] = $renamed;
                }
            }

            return $renamedRecords;
        }

        return $records;
    }

    /**
     * Serializing a resultset will dump all related rows into a big array
     */
    public function serialize() : string
    {
        $container = Di::getDefault();

        if(!is_object($container)) {
            throw new Exception(
                "The dependency injector container is not valid"
            );
        }

        $data = [
            "model"         => $this->model,
            "cache"         => $this->cache,
            "rows"          => $this->toArray(false),
            "columnMap"     => $this->columnMap,
            "hydrateMode"   => $this->hydrateMode,
            "keepSnapshots" => $this->keepSnapshots
        ];

        if($container->has("serializer")) {
            $serializer = $container->getShared("serializer");
            $serializer->setData($data);

            return $serializer->serialize();
        }

        /**
         * Serialize the cache using the serialize function
         */
        return serialize($data);
    }

    /**
     * Unserializing a resultset will allow to only works on the rows present in
     * the saved state
     */
    public function unserialize($data) : void
    {
        

        $container = Di::getDefault();

        if(!is_object($container)) {
            throw new Exception(
                "The dependency injector container is not valid"
            );
        }

        if($container->has("serializer")) {
            $serializer = $container->getShared("serializer");

            $serializer->unserialize($data);
            $resultset = $serializer->getData();
        } else {
            $resultset = unserialize($data);
        }

        if(!is_array($resultset)) {
            throw new Exception("Invalid serialization data");
        }

        $this->model       = $resultset["model"];
            $this->rows        = $resultset["rows"];
            $this->count       = count($resultset["rows"]);
            $this->cache       = $resultset["cache"];
            $this->columnMap   = $resultset["columnMap"];
            $this->hydrateMode = $resultset["hydrateMode"];
            $keepSnapshots = $resultset["keepSnapshots"] ?? null;
            
        if( $keepSnapshots !== null) {
            $this->keepSnapshots = $keepSnapshots;
        }
    }
}
