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

use Phalcon\Db\ResultInterface;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\Model\Row;
use Phalcon\Parsers\Parser;
use Phalcon\Support\Settings;
use stdClass;

use function get_class;
use function is_array;
use function serialize;
use function str_replace;
use function unserialize;

/**
 * Complex resultsets may include complete objects and scalar values.
 * This class builds every complex row as it is required
 */
class Complex extends Resultset implements ResultsetInterface
{
    /**
     * Unserialised result-set hydrated all rows already. unserialise() sets
     * disableHydration to true
     *
     * @var bool
     */
    protected bool $disableHydration = false;

    /**
     * Phalcon\Mvc\Model\Resultset\Complex constructor
     *
     * @param array|null           $columnTypes
     * @param ResultInterface|null $result
     * @param mixed|null           $cache
     *
     * @throws Exception
     */
    public function __construct(
        protected array | null $columnTypes,
        ResultInterface | null $result = null,
        mixed $cache = null
    ) {
        parent::__construct($result, $cache);
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        /**
         * Obtain the records as an array
         */
        $records = $this->toArray();

        $cache       = $this->cache;
        $columnTypes = $this->columnTypes;
        $hydrateMode = $this->hydrateMode;

        return [
            "cache"       => $cache,
            "rows"        => $records,
            "columnTypes" => $columnTypes,
            "hydrateMode" => $hydrateMode,
        ];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        /**
         * Rows are already hydrated
         */
        $this->disableHydration = true;

        $this->rows        = $data["rows"];
        $this->count       = count($data["rows"]);
        $this->cache       = $data["cache"];
        $this->columnTypes = $data["columnTypes"];
        $this->hydrateMode = (int)$data["hydrateMode"];
    }

    /**
     * Returns current row in the resultset
     *
     * @return mixed
     * @throws Exception
     */
    final public function current(): mixed
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
         * Resultset was unserialized, we do not need to hydrate
         */
        if ($this->disableHydration) {
            $this->activeRow = $row;

            return $row;
        }

        /**
         * Valid records are arrays
         */
        if (!is_array($row)) {
            $this->activeRow = false;

            return false;
        }

        /**
         * Get current hydration mode
         */
        $hydrateMode = $this->hydrateMode;

        /**
         * Each row in a complex result is a Phalcon\Mvc\Model\Row instance
         */
        $activeRow = match ($hydrateMode) {
            Resultset::HYDRATE_RECORDS => new Row(),
            Resultset::HYDRATE_ARRAYS  => [],
            default                    => new stdClass(),
        };

        /**
         * Set records as dirty state PERSISTENT by default
         */
        $dirtyState = 0;

        /**
         * Create every record according to the column types
         */
        foreach ($this->columnTypes as $alias => $column) {
            if (!is_array($column)) {
                throw new Exception("Column type is corrupt");
            }

            $type = $column["type"];

            if ($type == "object") {
                /**
                 * Object columns are assigned column by column
                 */
                $source     = $column["column"];
                $attributes = $column["attributes"];
                $columnMap  = $column["columnMap"];

                /**
                 * Assign the values from the _source_attribute notation to its real column name
                 */
                $rowModel = [];

                foreach ($attributes as $attribute) {
                    /**
                     * Columns are supposed to be in the form _table_field
                     */
                    $columnValue          = $row["_" . $source . "_" . $attribute];
                    $rowModel[$attribute] = $columnValue;
                }

                /**
                 * Generate the column value according to the hydration type
                 */
                if ($hydrateMode == Resultset::HYDRATE_RECORDS) {
                    // Check if the resultset must keep snapshots
                    $keepSnapshots = $column["keepSnapshots"] ?? false;

                    if (Settings::get("orm.late_state_binding")) {
                        if ($column["instance"] instanceof Model) {
                            $modelName = get_class($column["instance"]);
                        } else {
                            $modelName = "Phalcon\\Mvc\\Model";
                        }

                        $value = $modelName::cloneResultMap(
                            $column["instance"],
                            $rowModel,
                            $columnMap,
                            $dirtyState,
                            $keepSnapshots
                        );
                    } else {
                        /**
                         * Get the base instance. Assign the values to the
                         * attributes using a column map
                         */
                        $value = Model::cloneResultMap(
                            $column["instance"],
                            $rowModel,
                            $columnMap,
                            $dirtyState,
                            $keepSnapshots
                        );
                    }
                } else {
                    // Other kinds of hydration
                    $value = Model::cloneResultMapHydrate(
                        $rowModel,
                        $columnMap,
                        $hydrateMode
                    );
                }

                /**
                 * The complete object is assigned to an attribute with the name of the alias or the model name
                 */
                $attribute = $column["balias"];
            } else {
                /**
                 * Scalar columns are simply assigned to the result object
                 */
                if (isset($column["sqlAlias"])) {
                    $value = $row[$column["sqlAlias"]];
                } else {
                    $value = $row[$alias];
                }

                /**
                 * If a "balias" is defined is not an unnamed scalar
                 */
                if (isset($column["balias"])) {
                    $attribute = $alias;
                } else {
                    $attribute = str_replace("_", "", $alias);
                }
            }

            if (!isset($column["eager"])) {
                /**
                 * Assign the instance according to the hydration type
                 */
                if ($hydrateMode == Resultset::HYDRATE_ARRAYS) {
                    $activeRow[$attribute] = $value;
                } else {
                    $activeRow->$attribute = $value;
                }
            }
        }

        /**
         * Store the generated row in this_ptr->activeRow to be retrieved by 'current'
         */
        $this->activeRow = $activeRow;

        return $activeRow;
    }

    /**
     * Serializing a resultset will dump all related rows into a big array,
     * serialize it and return the resulting string
     *
     * @return string
     * @throws Exception
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
            "cache"       => $this->cache,
            "rows"        => $this->toArray(),
            "columnTypes" => $this->columnTypes,
            "hydrateMode" => $this->hydrateMode,
        ];

        if ($container->has("serializer")) {
            $serializer = $container->getShared("serializer");
            $serializer->setData($data);

            return $serializer->serialize();
        }

        return serialize($data);
    }

    /**
     * Returns a complete resultset as an array, if the resultset has a big
     * number of rows it could consume more memory than currently it does.
     *
     * @return array
     */
    public function toArray(): array
    {
        $records = [];

        $this->rewind();

        while ($this->valid()) {
            $current   = $this->current();
            $records[] = $current;

            $this->next();
        }

        return $records;
    }

    /**
     * Unserializing a resultset will allow to only works on the rows present
     * in the saved state
     *
     * @param mixed $data
     *
     * @return void
     * @throws Exception
     */
    public function unserialize(mixed $data): void
    {
        /**
         * Rows are already hydrated
         */
        $this->disableHydration = true;

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

        $this->rows        = $resultset["rows"];
        $this->count       = count($resultset["rows"]);
        $this->cache       = $resultset["cache"];
        $this->columnTypes = $resultset["columnTypes"];
        $this->hydrateMode = (int)$resultset["hydrateMode"];
    }
}
