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

namespace Phalcon\Mvc\Model\Resultset;

use Phalcon\Db\Enum;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Row;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;

use function get_class;
use function is_array;
use function is_string;
use function serialize;
use function unserialize;

/**
 * Simple resultsets only contains a complete objects
 * This class builds every complete object as it is required
 */
class Simple extends Resultset
{
    /**
     * Phalcon\Mvc\Model\Resultset\Simple constructor
     *
     * @param array|string          $columnMap
     * @param ModelInterface|Row    $model
     * @param ResultInterface|false $result
     * @param mixed|null            $cache
     * @param bool                  $keepSnapshots
     *
     * @throws Exception
     */
    public function __construct(
        protected mixed $columnMap,
        protected mixed $model,
        mixed $result,
        mixed $cache = null,
        protected bool $keepSnapshots = false
    ) {
        parent::__construct($result, $cache);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function __serialize(): array
    {
        return [
            "model"         => $this->model,
            "cache"         => $this->cache,
            "rows"          => $this->toArray(false),
            "columnMap"     => $this->columnMap,
            "hydrateMode"   => $this->hydrateMode,
            "keepSnapshots" => $this->keepSnapshots,
        ];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->model       = $data["model"];
        $this->rows        = $data["rows"];
        $this->count       = count($data["rows"]);
        $this->cache       = $data["cache"];
        $this->columnMap   = $data["columnMap"];
        $this->hydrateMode = (int)$data["hydrateMode"];

        if (isset($data["keepSnapshots"])) {
            $this->keepSnapshots = $data["keepSnapshots"];
        }
    }

    /**
     * Returns current row in the resultset
     *
     * @return ModelInterface|null
     */
    final public function current(): ModelInterface | Row | null
    {
        $activeRow = $this->activeRow;

        if ($activeRow !== null) {
            return $activeRow;
        }

        /**
         * Current row is set by seek() operations
         */
        $row = $this->row;

        /**
         * Valid records are arrays
         */
        if (!is_array($row)) {
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
        if ($hydrateMode == Resultset::HYDRATE_RECORDS) {
            /**
             * Set records as dirty state PERSISTENT by default
             * Performs the standard hydration based on objects
             */
            if (Settings::get("orm.late_state_binding")) {
                if ($this->model instanceof Model) {
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
        } else {
            /**
             * Other kinds of hydrations
             */
            $activeRow = Model::cloneResultMapHydrate(
                $row,
                $columnMap,
                $hydrateMode
            );
        }

        $this->activeRow = $activeRow;

        return $activeRow;
    }

    /**
     * Serializing a resultset will dump all related rows into a big array
     */
    public function serialize(): string
    {
        $container = Di::getDefault();
        if ($container === null) {
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
            "keepSnapshots" => $this->keepSnapshots,
        ];

        if ($container->has("serializer")) {
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
     * Returns a complete resultset as an array, if the resultset has a big
     * number of rows it could consume more memory than currently it does.
     * Export the resultset to an array couldn't be faster with a large number
     * of records
     */
    public function toArray(bool $renameColumns = true): array
    {
        /**
         * If _rows is not present, fetchAll from database
         * and keep them in memory for further operations
         */
        $records = $this->rows;

        if (!is_array($records)) {
            $result = $this->result;

            if ($this->row !== null) {
                // re-execute query if required and fetchAll rows
                $result->execute();
            }

            $records = $result->fetchAll(Enum::FETCH_ASSOC);

            $this->row  = null;
            $this->rows = $records; // keep result-set in memory
        }

        /**
         * We need to rename the whole set here, this could be slow
         *
         * Only rename when it is Model
         */
        if ($renameColumns && !($this->model instanceof Row)) {
            if (!is_array($this->columnMap)) {
                return $records;
            }

            $renamedRecords = [];
            if (is_array($records)) {
                foreach ($records as $record) {
                    $renamed = [];
                    foreach ($record as $key => $value) {
                        if (is_string($key)) {
                            /**
                             * Check if the key is part of the column map
                             */
                            if (!isset($this->columnMap[$key])) {
                                throw new Exception(
                                    "Column '" . $key . "' is not part of the column map"
                                );
                            }

                            $renamedKey = $this->columnMap[$key];

                            if (is_array($renamedKey)) {
                                if (!isset($renamedKey[0])) {
                                    throw new Exception(
                                        "Column '" . $key . "' is not part of the column map"
                                    );
                                }

                                $renamedKey = $renamedKey[0];
                            }

                            $renamed[$renamedKey] = $value;
                        }
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
     * Unserializing a resultset will allow to only works on the rows present in
     * the saved state
     */
    public function unserialize(string $data): void
    {
        $container = Di::getDefault();
        if ($container === null) {
            throw new Exception(
                "The dependency injector container is not valid"
            );
        }

        if ($container->has("serializer")) {
            $serializer = $container->getShared("serializer");

            $serializer->unserialize($data);
            $resultset = $serializer->getData();
        } else {
            $resultset = unserialize($data);
        }

        if (!is_array($resultset)) {
            throw new Exception("Invalid serialization data");
        }

        $this->model       = $resultset["model"];
        $this->rows        = $resultset["rows"];
        $this->count       = count($resultset["rows"]);
        $this->cache       = $resultset["cache"];
        $this->columnMap   = $resultset["columnMap"];
        $this->hydrateMode = (int)$resultset["hydrateMode"];

        if (isset($resultset["keepSnapshots"])) {
            $this->keepSnapshots = $resultset["keepSnapshots"];
        }
    }
}
