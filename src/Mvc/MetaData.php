<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 */

namespace Phalcon\Mvc;

use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\MetaData\Strategy\Introspection;
use Phalcon\Mvc\Model\MetaData\Strategy\StrategyInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\MetaData
 *
 * Because Phalcon\Mvc\Model requires meta-data like field names, data types,
 * primary keys, etc. This component collect them and store for further
 * querying by Phalcon\Mvc\Model. Phalcon\Mvc\Model\MetaData can also use
 * adapters to store temporarily or permanently the meta-data.
 *
 * A standard Phalcon\Mvc\Model\MetaData can be used to query model attributes:
 *
 * ```php
 * $metaData = new \Phalcon\Mvc\Model\MetaData\Memory();
 *
 * $attributes = $metaData->getAttributes(
 *     new Robots()
 * );
 *
 * print_r($attributes);
 * ```
 */
abstract class MetaData implements InjectionAwareInterface, MetaDataInterface
{
    const MODELS_ATTRIBUTES = 0;
    const MODELS_AUTOMATIC_DEFAULT_INSERT = 10;
    const MODELS_AUTOMATIC_DEFAULT_UPDATE = 11;
    const MODELS_COLUMN_MAP = 0;
    const MODELS_DATE_AT = 6;
    const MODELS_DATE_IN = 7;
    const MODELS_DATA_TYPES = 4;
    const MODELS_DATA_TYPES_BIND = 9;
    const MODELS_DATA_TYPES_NUMERIC = 5;
    const MODELS_DEFAULT_VALUES = 12;
    const MODELS_EMPTY_STRING_VALUES = 13;
    const MODELS_IDENTITY_COLUMN = 8;
    const MODELS_NON_PRIMARY_KEY = 2;
    const MODELS_NOT_NULL = 3;
    const MODELS_PRIMARY_KEY = 1;
    const MODELS_REVERSE_COLUMN_MAP = 1;

    /**
     * @var CacheAdapterInterface
     */
    protected $adapter;

    protected $columnMap = [];

    protected $container;

    protected $metaData = [];

    protected $strategy;

    /**
     * Returns table attributes names (fields)
     *
     *```php
     * print_r(
     *     $metaData->getAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getAttributes(ModelInterface $model) : array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_ATTRIBUTES);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes that must be ignored from the INSERT SQL generation
     *
     *```php
     * print_r(
     *     $metaData->getAutomaticCreateAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getAutomaticCreateAttributes(ModelInterface $model) : array
    {
            $data = $this->readMetaDataIndex(
            $model,
            self::MODELS_AUTOMATIC_DEFAULT_INSERT
        );

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes that must be ignored from the UPDATE SQL generation
     *
     *```php
     * print_r(
     *     $metaData->getAutomaticUpdateAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getAutomaticUpdateAttributes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex(
            $model,
            self::MODELS_AUTOMATIC_DEFAULT_UPDATE
        );

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes and their bind data types
     *
     *```php
     * print_r(
     *     $metaData->getBindTypes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getBindTypes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex(
            $model,
            self::MODELS_DATA_TYPES_BIND
        );

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns the column map if any
     *
     *```php
     * print_r(
     *     $metaData->getColumnMap(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getColumnMap(ModelInterface $model) :  array | null
    {
            $data = $this->readColumnMapIndex($model, self::MODELS_COLUMN_MAP);

        if ($data !== null && !is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes (which have default values) and their default values
     *
     *```php
     * print_r(
     *     $metaData->getDefaultValues(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getDefaultValues(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex($model, self::MODELS_DEFAULT_VALUES);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes and their data types
     *
     *```php
     * print_r(
     *     $metaData->getDataTypes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getDataTypes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns attributes which types are numerical
     *
     *```php
     * print_r(
     *     $metaData->getDataTypesNumeric(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getDataTypesNumeric(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex(
            $model,
            self::MODELS_DATA_TYPES_NUMERIC
        );

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI() :  DiInterface
    {
            $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound("internal services")
            );
        }

        return $container;
    }

    /**
     * Returns attributes allow empty strings
     *
     *```php
     * print_r(
     *     $metaData->getEmptyStringAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getEmptyStringAttributes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex(
            $model,
            self::MODELS_EMPTY_STRING_VALUES
        );

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns the name of identity field (if one is present)
     *
     *```php
     * print_r(
     *     $metaData->getIdentityField(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getIdentityField(ModelInterface $model) :  string | null
    {
        return $this->readMetaDataIndex($model, self::MODELS_IDENTITY_COLUMN);
    }

    /**
     * Returns an array of fields which are not part of the primary key
     *
     *```php
     * print_r(
     *     $metaData->getNonPrimaryKeyAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getNonPrimaryKeyAttributes(ModelInterface $model) :  array | null
    {
            $data = $this->readMetaDataIndex($model, self::MODELS_NON_PRIMARY_KEY);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns an array of not null attributes
     *
     *```php
     * print_r(
     *     $metaData->getNotNullAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getNotNullAttributes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex($model, self::MODELS_NOT_NULL);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns an array of fields which are part of the primary key
     *
     *```php
     * print_r(
     *     $metaData->getPrimaryKeyAttributes(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getPrimaryKeyAttributes(ModelInterface $model) :  array
    {
            $data = $this->readMetaDataIndex($model, self::MODELS_PRIMARY_KEY);

        if (!is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Returns the reverse column map if any
     *
     *```php
     * print_r(
     *     $metaData->getReverseColumnMap(
     *         new Robots()
     *     )
     * );
     *```
     */
    public function getReverseColumnMap(ModelInterface $model) :  array | null
    {
            $data = $this->readColumnMapIndex(
            $model,
            self::MODELS_REVERSE_COLUMN_MAP
        );

        if ($data !== null && !is_array($data)) {
            throw new Exception("The meta-data is invalid or is corrupt");
        }

        return $data;
    }

    /**
     * Return the strategy to obtain the meta-data
     */
    public function getStrategy() : StrategyInterface
    {
        if ($this->strategy === null) {
                $this->strategy = new Introspection();
        }

        return $this->strategy;
    }

    /**
     * Check if a model has certain attribute
     *
     *```php
     * var_dump(
     *     $metaData->hasAttribute(
     *         new Robots(),
     *         "name"
     *     )
     * );
     *```
     */
    public function hasAttribute(ModelInterface $model, string $attribute) :  bool
    {
            $columnMap = $this->getReverseColumnMap($model);

        if (is_array($columnMap)) {
            return isset ($columnMap[$attribute]);
        }

        return isset ($this->readMetaData($model)[self::MODELS_DATA_TYPES][$attribute]);
    }

    /**
     * Checks if the internal meta-data container is empty
     *
     *```php
     * var_dump(
     *     $metaData->isEmpty()
     * );
     *```
     */
    public function isEmpty() :bool
    {
        return count($this->metaData) == 0;
    }

    /**
     * Reads metadata from the adapter
     */
    public function read(string $key) : array | null
    {
        return $this->adapter->get($key);
    }

    /**
     * Reads the ordered/reversed column map for certain model
     *
     *```php
     * print_r(
     *     $metaData->readColumnMap(
     *         new Robots()
     *     )
     * );
     *```
     */
    final public function readColumnMap(ModelInterface $model) :  array | null
    {
        if (!\globals_get("orm.column_renaming")) {
            return null;
        }

        $keyName = \get_class_lower($model);
        if (!isset($this->columnMap[$keyName])) {
            $this->initialize($model, null, null, null);
        }
        return  $this->columnMap[$keyName] ?? null;
    }

    /**
     * Reads column-map information for certain model using a MODEL_* constant
     *
     *```php
     * print_r(
     *     $metaData->readColumnMapIndex(
     *         new Robots(),
     *         MetaData::MODELS_REVERSE_COLUMN_MAP
     *     )
     * );
     *```
     */
    final public function readColumnMapIndex(ModelInterface $model, int $index) : mixed
    {
        if (!\globals_get("orm.column_renaming")) {
            return null;
        }
        $keyName = \get_class_lower($model);
        if (isset($this->columnMap[$keyName])) {
            $this->initialize($model, null, null, null);
        }
        $columnMapModel = $this->columnMap[$keyName] ?? null;
        if ($columnMapModel !== null) {
            return $columnMapModel[$index] ?? null;
        }
        return null;
    }

    /**
     * Reads the complete meta-data for certain model
     *
     *```php
     * print_r(
     *     $metaData->readMetaData(
     *         new Robots()
     *     )
     * );
     *```
     */
    final public function readMetaData(ModelInterface $model) : array | null
    {
            $source = $model->getSource();
            $schema = $model->getSchema();

        /*
         * Unique key for meta-data is created using class-name-schema-source
         */
        $key = \get_class_lower($model) . "-" . $schema . $source;

        if (!isset($this->metaData[$key])) {
            $this->initialize($model, $key, $source, $schema);
        }

        return $this->metaData[$key] ?? null;
    }

    /**
     * Reads meta-data for certain model
     *
     *```php
     * print_r(
     *     $metaData->readMetaDataIndex(
     *         new Robots(),
     *         0
     *     )
     * );
     *```
     */
    final public function readMetaDataIndex(ModelInterface $model, int $index) : mixed
    {
        $source = $model->getSource();
        $schema = $model->getSchema();

        /*
         * Unique key for meta-data is created using class-name-schema-source
         */
        $key = \get_class_lower($model) . "-" . $schema . $source;

        if (!isset ($this->metaData[$key][$index])) {
            $this->initialize($model, $key, $source, $schema);
        }

        return $this->metaData[$key][$index] ?? null;
    }

    /**
     * Resets internal meta-data in order to regenerate it
     *
     *```php
     * $metaData->reset();
     *```
     */
    public function reset() : void
    {
            $this->metaData = [];
            $this->columnMap = [];
    }

    /**
     * Set the $attributes that must be ignored from the INSERT SQL generation
     *
     *```php
     * $metaData->setAutomaticCreateAttributes(
     *     new Robots(),
     *     [
     *         "created_at" => true,
     *     ]
     * );
     *```
     */
    public function setAutomaticCreateAttributes(ModelInterface $model, array $attributes) : void
    {
        $this->writeMetaDataIndex(
            $model,
            self::MODELS_AUTOMATIC_DEFAULT_INSERT,
            $attributes
        );
    }

    /**
     * Set the attributes that must be ignored from the UPDATE SQL generation
     *
     *```php
     * $metaData->setAutomaticUpdateAttributes(
     *     new Robots(),
     *     [
     *         "modified_at" => true,
     *     ]
     * );
     *```
     */
    public function setAutomaticUpdateAttributes(ModelInterface $model, array $attributes) : void
    {
        $this->writeMetaDataIndex(
            $model,
            self::MODELS_AUTOMATIC_DEFAULT_UPDATE,
            $attributes
        );
    }

    /**
     * Set the attributes that allow empty string values
     *
     *```php
     * $metaData->setEmptyStringAttributes(
     *     new Robots(),
     *     [
     *         "name" => true,
     *     ]
     * );
     *```
     */
    public function setEmptyStringAttributes(ModelInterface $model, array $attributes) : void
    {
        $this->writeMetaDataIndex(
            $model,
            self::MODELS_EMPTY_STRING_VALUES,
            $attributes
        );
    }

    /**
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $container) : void
    {
            $this->container = $container;
    }

    /**
     * Set the meta-data extraction strategy
     */
    public function setStrategy(StrategyInterface $strategy) : void
    {
            $this->strategy = $strategy;
    }

    /**
     * Writes the metadata to adapter
     */
    public function write(string $key, array $data) : void
    {
        try {
            $option = globals_get("orm.exception_on_failed_metadata_save");
            $result = $this->adapter->set($key, $data);

            if (false === $result) {
                $this->throwWriteException($option);
            }
        } catch (\Exception) {
            $this->throwWriteException($option);
        }
    }

    /**
     * Writes meta-data for certain $model using a MODEL_* constant
     *
     *```php
     * print_r(
     *     $metaData->writeColumnMapIndex(
     *         new Robots(),
     *         MetaData::MODELS_REVERSE_COLUMN_MAP,
     *         [
     *             "leName" => "name",
     *         ]
     *     )
     * );
     *```
     */
    final public function writeMetaDataIndex(ModelInterface $model, int $index, $data) : void
    {
        if (!is_array($data) && !is_string($data) && !is_bool($data)) {
            throw new Exception("Invalid data for index");
        }

            $source = $model->getSource();
            $schema = $model->getSchema();

        /*
         * Unique key for meta-data is created using class-name-schema-table
         */
            $key = \get_class_lower($model) . "-" . $schema . $source;

        if (!isset($this->metaData[$key])) {
            $this->initialize($model, $key, $source, $schema);
        }

            $this->metaData[$key][$index] = $data;
    }

    /**
     * Initialize the metadata for certain table
     */
    final protected function initialize(ModelInterface $model, $key, $table, $schema) : void
    {
            $strategy = null;
            $className = get_class($model);

        if ($key !== null) {
                $metaData = $this->metaData;

            if (!isset($metaData[$key])){
                /**
                 * The meta-data is read from the adapter always if not available in metaData property
                 */
                    $prefixKey = "meta-" . $key;
                    $data = $this->{"read"}($prefixKey);

                if ($data !== null) {
                        $this->metaData[$key] = $data;
                } else {
                    /**
                     * Check if there is a method 'metaData' in the model to retrieve meta-data from it
                     */
                    if (method_exists($model, "metaData")) {
                            $modelMetadata = $model->{"metaData"}();

                        if (!is_array($modelMetadata)) {
                            throw new Exception(
                                "Invalid meta-data for model " . $className
                            );
                        }
                    } else {
                        /**
                         * Get the meta-data extraction strategy
                         */
                            $container = $this->getDI();
                            $strategy = $this->getStrategy();
                            $modelMetadata = $strategy->getMetaData(
                                $model,
                                $container
                            );
                    }

                    /**
                     * Store the meta-data locally
                     */
                        $this->metaData[$key] = $modelMetadata;

                    /**
                     * Store the meta-data in the adapter
                     */
                    $this->{"write"}($prefixKey, $modelMetadata);
                }
            }
        }

        /**
         * Check for a column map, store in columnMap in order and reversed order
         */
        if (!\globals_get("orm.column_renaming")) {
            return;
        }

        $keyName = strtolower($className);

        if (isset($this->columnMap[$keyName])) {
            return;
        }

        /**
         * Create the map key name
         * Check if the meta-data is already in the adapter
         */
            $prefixKey = "map-" . $keyName;
            $data = $this->{"read"}($prefixKey);

        if ($data !== null) {
            $this->columnMap[$keyName] = $data;
            return;
        }

        /**
         * Get the meta-data extraction strategy
         */
        if (!is_object($strategy)) {
                $container = $this->getDI();
                $strategy = $this->getStrategy();
        }

        /**
         * Get the meta-data
         * Update the column map locally
         */
        $modelColumnMap = $strategy->getColumnMaps($model, $container);
        $this->columnMap[$keyName] = $modelColumnMap;

        /**
         * Write the data to the adapter
         */
        $this->{"write"}($prefixKey, $modelColumnMap);
    }

    /**
     * Throws an exception when the metadata cannot be written
     */
    private function throwWriteException($option) : void
    {
        $message = "Failed to store metaData to the cache adapter";

        if ($option) {
            throw new Exception($message);
        } else {
            trigger_error($message);
        }
    }
}
