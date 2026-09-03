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

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Eager\Loader;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Exceptions\InvalidContainer;
use Phalcon\Mvc\Model\Exceptions\InvalidSerializationData;
use Phalcon\Mvc\Model\Exceptions\ResultsetColumnNotInMap;
use Phalcon\Mvc\Model\ResultInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Row;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;

use function count;
use function get_class;
use function is_array;
use function is_string;
use function serialize;
use function unserialize;

/**
 * Simple resultsets only contains a complete objects
 * This class builds every complete object as it is required
 *
 * @extends Resultset<int, mixed>
 *
 * @phpstan-import-type mvc_eager_map from MvcTypes
 * @phpstan-import-type mvc_hydration_column_map from MvcTypes
 * @phpstan-import-type mvc_resultset_simple_state from MvcTypes
 */
class Simple extends Resultset
{
    /**
     * @var array|null
     *
     * @phpstan-var mvc_eager_map|null
     */
    protected array | null $eagerMap = null;

    /**
     * Phalcon\Mvc\Model\Resultset\Simple constructor
     *
     * @param array|string          $columnMap
     * @param ModelInterface|Row    $model
     * @param false|ResultInterface $result
     * @param mixed|null            $cache
     * @param bool                  $keepSnapshots
     *
     * @throws Exception
     *
     * @phpstan-param mvc_hydration_column_map|string|null      $columnMap
     * @phpstan-param \Phalcon\Contracts\Db\Result|false|null $result
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
     *
     * @phpstan-return mvc_resultset_simple_state
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
     *
     * @phpstan-param mvc_resultset_simple_state $data
     */
    public function __unserialize(array $data): void
    {
        $this->model       = $data["model"];
        $this->rows        = $data["rows"];
        $this->count       = count($data["rows"]);
        $this->cache       = $data["cache"];
        $this->columnMap   = $data["columnMap"];
        $this->hydrateMode = $data["hydrateMode"];

        if (isset($data["keepSnapshots"])) {
            $this->keepSnapshots = $data["keepSnapshots"];
        }
    }

    /**
     * Returns current row in the resultset
     *
     * @return ModelInterface|null
     *
     * @phpstan-return ModelInterface|Row|null
     */
    final public function current(): ModelInterface | Row | null
    {
        /** @var ModelInterface|Row|null $activeRow */
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

            if ($this->eagerMap !== null) {
                // cloneResultMap() hydrates an instance of the model class.
                /** @var Model $activeRow */
                Loader::apply($activeRow, $this->eagerMap);
            }
        } else {
            /**
             * Other kinds of hydrations. Eagerly loaded relations are
             * deliberately not applied here: arrays and standard objects have
             * neither a relation cache nor writeAttribute().
             */
            $activeRow = Model::cloneResultMapHydrate(
                $row,
                $columnMap,
                $hydrateMode
            );
        }

        $this->activeRow = $activeRow;

        /** @var ModelInterface|Row|null */
        return $activeRow;
    }

    /**
     * Serializing a resultset will dump all related rows into a big array
     */
    public function serialize(): string
    {
        $container = Di::getDefault();
        if ($container === null) {
            throw new InvalidContainer();
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
            if ($container instanceof DiInterface) {
                $serializer = $container->getShared("serializer");
            } else {
                $serializer = $container->get("serializer");
            }
            /** @var \Phalcon\Storage\Serializer\SerializerInterface $serializer */
            $serializer->setData($data);

            /** @var string */
            return $serializer->serialize();
        }

        /**
         * Serialize the cache using the serialize function
         */
        return serialize($data);
    }

    /**
     * Attaches a pre-loaded relation map, applied to every record as it is
     * hydrated.
     *
     * Records in a resultset are transient - seek() clears activeRow on every
     * move and current() re-hydrates from the raw row - so hydration is the
     * only durable point at which relations can be stamped.
     *
     * @param array $eagerMap
     *
     * @return void
     *
     * @phpstan-param mvc_eager_map $eagerMap
     */
    public function setEagerMap(array $eagerMap): void
    {
        $this->eagerMap = $eagerMap;
    }

    /**
     * Builds a new resultset of the same concrete class over the rows at the
     * given positions, preserving the column map, record prototype and
     * snapshot behavior of this resultset.
     *
     * @param array $indexes zero-based row positions, in the desired order
     *
     * @return Simple
     *
     * @phpstan-param array<array-key, int> $indexes
     */
    public function sliceRows(array $indexes): Simple
    {
        $this->materialize();

        $sliced = [];

        foreach ($indexes as $index) {
            if (isset($this->rows[$index])) {
                $sliced[] = $this->rows[$index];
            }
        }

        $class = get_class($this);

        $resultset = new $class(
            $this->columnMap,
            $this->model,
            false,
            null,
            $this->keepSnapshots
        );

        $resultset->rows  = $sliced;
        $resultset->count = count($sliced);

        return $resultset;
    }

    /**
     * Returns a complete resultset as an array, if the resultset has a big
     * number of rows it could consume more memory than currently it does.
     * Export the resultset to an array couldn't be faster with a large number
     * of records
     *
     * @phpstan-return array<array-key, array<array-key, mixed>>
     */
    public function toArray(bool $renameColumns = true): array
    {
        /**
         * If the rows are not present, fetch them from the database and keep
         * them in memory for further operations
         */
        $this->materialize();

        $records = $this->rows;

        /**
         * We need to rename the whole set here, this could be slow
         *
         * Only rename when it is Model
         */
        if ($renameColumns && !($this->model instanceof Row)) {
            if (!is_array($this->columnMap)) {
                /** @var array<array-key, array<array-key, mixed>> */
                return $records;
            }

            $renamedRecords = [];
            if (is_array($records)) {
                // The rows of a resultset are attribute-keyed arrays.
                /** @var array<array-key, array<array-key, mixed>> $records */
                foreach ($records as $record) {
                    $renamed = [];
                    foreach ($record as $key => $value) {
                        if (is_string($key)) {
                            /**
                             * Check if the key is part of the column map
                             */
                            if (!isset($this->columnMap[$key])) {
                                throw new ResultsetColumnNotInMap($key);
                            }

                            $renamedKey = $this->columnMap[$key];

                            if (is_array($renamedKey)) {
                                if (!isset($renamedKey[0])) {
                                    throw new ResultsetColumnNotInMap($key);
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

        /** @var array<array-key, array<array-key, mixed>> */
        return $records;
    }

    /**
     * Unserializing a resultset will allow to only works on the rows present in
     * the saved state
     *
     * @phpstan-param string $data
     */
    public function unserialize(mixed $data): void
    {
        $container = Di::getDefault();
        if ($container === null) {
            throw new InvalidContainer();
        }

        if ($container->has("serializer")) {
            if ($container instanceof DiInterface) {
                $serializer = $container->getShared("serializer");
            } else {
                $serializer = $container->get("serializer");
            }

            /** @var \Phalcon\Storage\Serializer\SerializerInterface $serializer */
            $serializer->unserialize($data);
            $resultset = $serializer->getData();
        } else {
            $resultset = unserialize($data);
        }

        if (!is_array($resultset)) {
            throw new InvalidSerializationData();
        }

        /** @var mvc_resultset_simple_state $resultset */

        $this->model       = $resultset["model"];
        $this->rows        = $resultset["rows"];
        $this->count       = count($resultset["rows"]);
        $this->cache       = $resultset["cache"];
        $this->columnMap   = $resultset["columnMap"];
        $this->hydrateMode = $resultset["hydrateMode"];

        if (isset($resultset["keepSnapshots"])) {
            $this->keepSnapshots = $resultset["keepSnapshots"];
        }
    }
}
