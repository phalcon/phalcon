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

namespace Phalcon\Mvc\Model;

use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\MetaData\Strategy\Introspection;
use Phalcon\Mvc\Model\MetaData\Strategy\StrategyInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Parsers\Parser;
use Phalcon\Support\Settings;
use Phalcon\Traits\Php\IniTrait;

use function get_class;
use function is_array;
use function method_exists;
use function spl_object_id;
use function trigger_error;

/**
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
abstract class MetaData extends Injectable implements MetaDataInterface
{
    use IniTrait;

    private const MESSAGE_INVALID_METADATA = "The meta-data is invalid or is corrupt";
    public const MODELS_ATTRIBUTES               = 0;
    public const MODELS_AUTOMATIC_DEFAULT_INSERT = 10;
    public const MODELS_AUTOMATIC_DEFAULT_UPDATE = 11;
    public const MODELS_COLUMN_MAP               = 0;
    public const MODELS_DATA_TYPES               = 4;
    public const MODELS_DATA_TYPES_BIND          = 9;
    public const MODELS_DATA_TYPES_NUMERIC       = 5;
    public const MODELS_DATE_AT                  = 6;
    public const MODELS_DATE_IN                  = 7;
    public const MODELS_DEFAULT_VALUES           = 12;
    public const MODELS_EMPTY_STRING_VALUES      = 13;
    public const MODELS_IDENTITY_COLUMN          = 8;
    public const MODELS_NON_PRIMARY_KEY          = 2;
    public const MODELS_NOT_NULL                 = 3;
    public const MODELS_PRIMARY_KEY              = 1;
    public const MODELS_REVERSE_COLUMN_MAP       = 1;
    /**
     * @var CacheAdapterInterface|null
     */
    protected CacheAdapterInterface | null $adapter = null;

    /**
     * @var array
     */
    protected array $columnMap = [];

    /**
     * @var DiInterface|null
     */
    protected DiInterface | null $container = null;

    /**
     * @var array
     */
    protected array $metaData = [];

    /**
     * @var StrategyInterface|null
     */
    protected StrategyInterface | null $strategy = null;

    /**
     * Return the internal cache adapter
     *
     * @return CacheAdapterInterface|null
     */
    public function getAdapter(): CacheAdapterInterface | null
    {
        return $this->adapter;
    }

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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_ATTRIBUTES);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
    }

    /**
     * Returns attributes that must be ignored from the INSERT SQL generation
     *
     *```php
     * print_r(eadColumnMapIndex)
     *     )
     * );
     *```
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getAutomaticCreateAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_AUTOMATIC_DEFAULT_INSERT);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getAutomaticUpdateAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_AUTOMATIC_DEFAULT_UPDATE);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getBindTypes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES_BIND);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array|null
     * @throws Exception
     */
    public function getColumnMap(ModelInterface $model): array | null
    {
        $data = $this->readColumnMapIndex($model, self::MODELS_COLUMN_MAP);

        if (is_array($data) || null === $data) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
    }

    /**
     * Returns a ColumnMap Unique key for meta-data is created using className
     *
     * @param ModelInterface $model
     *
     * @return string|null
     * @throws Exception
     */
    final public function getColumnMapUniqueKey(ModelInterface $model): string | null
    {
        $key = mb_strtolower(get_class($model));
        if (
            false === isset($this->columnMap[$key]) &&
            false === $this->initializeColumnMap($model, $key)
        ) {
            return null;
        }

        return $key;
    }

    /**
     * Returns the DependencyInjector container
     *
     * @return DiInterface
     * @throws Exception
     */
    public function getDI(): DiInterface
    {
        $this->checkContainer(
            Exception::class,
            'internal services'
        );

        return $this->container;
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getDataTypes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getDataTypesNumeric(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES_NUMERIC);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getDefaultValues(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DEFAULT_VALUES);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getEmptyStringAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_EMPTY_STRING_VALUES);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return bool|string|null
     * @throws Exception
     */
    public function getIdentityField(ModelInterface $model): bool | string | null
    {
        return $this->readMetaDataIndex($model, self::MODELS_IDENTITY_COLUMN);
    }

    /**
     * Returns a MetaData Unique key for meta-data is created using className
     *
     * @param ModelInterface $model
     *
     * @return string|null
     * @throws Exception
     */
    final public function getMetaDataUniqueKey(ModelInterface $model): string | null
    {
        $key = mb_strtolower(get_class($model));
        if (
            false === isset($this->metaData[$key]) &&
            false === $this->initializeMetaData($model, $key)
        ) {
            return null;
        }

        return $key;
    }

    /**
     * Returns the model UniqueID based on model and array row primary key(s) value(s)
     *
     * @param ModelInterface $model
     * @param array          $row
     *
     * @return string|null
     * @throws Exception
     */
    public function getModelUUID(ModelInterface $model, array $row): string | null
    {
        $pks = $this->readMetaDataIndex($model, self::MODELS_PRIMARY_KEY);
        if (null === $pks) {
            return null;
        }

        $uuid = get_class($model);

        foreach ($pks as $pk) {
            $uuid = $uuid . ':' . $row[$pk];
        }

        return $uuid;
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getNonPrimaryKeyAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_NON_PRIMARY_KEY);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getNotNullAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_NOT_NULL);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array
     * @throws Exception
     */
    public function getPrimaryKeyAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_PRIMARY_KEY);
        if (is_array($data)) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
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
     *
     * @param ModelInterface $model
     *
     * @return array|null
     * @throws Exception
     */
    public function getReverseColumnMap(ModelInterface $model): array | null
    {
        $data = $this->readColumnMapIndex($model, self::MODELS_REVERSE_COLUMN_MAP);
        if (is_array($data) || null === $data) {
            return $data;
        }

        throw new Exception(self::MESSAGE_INVALID_METADATA);
    }

    /**
     * Return the strategy to obtain the meta-data
     *
     * @return StrategyInterface
     */
    public function getStrategy(): StrategyInterface
    {
        if (null === $this->strategy) {
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
     *
     * @param ModelInterface $model
     * @param string         $attribute
     *
     * @return bool
     * @throws Exception
     */
    public function hasAttribute(ModelInterface $model, string $attribute): bool
    {
        $columnMap = $this->getReverseColumnMap($model);
        if (is_array($columnMap)) {
            return isset($columnMap[$attribute]);
        }

        return isset($this->readMetaData($model)[self::MODELS_DATA_TYPES][$attribute]);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->metaData);
    }

    /**
     * Compares if two models are the same in memory
     *
     * @param ModelInterface $first
     * @param ModelInterface $other
     *
     * @return bool
     */
    public function modelEquals(ModelInterface $first, ModelInterface $other): bool
    {
        return spl_object_id($first) === spl_object_id($other);
    }

    /**
     * Reads metadata from the adapter
     *
     * @param string|null $key
     *
     * @return array|null
     */
    public function read(string | null $key): array | null
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
     *
     * @param ModelInterface $model
     *
     * @return array|null
     * @throws Exception
     */
    final public function readColumnMap(ModelInterface $model): array | null
    {
        if (Settings::get("orm.column_renaming")) {
            return null;
        }

        $keyName = $this->getColumnMapUniqueKey($model);
        if ($keyName !== null) {
            return $this->columnMap[$keyName];
        }

        return null;
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
     *
     * @param ModelInterface $model
     * @param int            $index
     *
     * @return array|null
     * @throws Exception
     */
    final public function readColumnMapIndex(ModelInterface $model, int $index): array | null
    {
        if (true !== Settings::get('orm.column_renaming')) {
            return null;
        }

        $keyName = $this->getColumnMapUniqueKey($model);
        if ($keyName !== null) {
            return $this->columnMap[$keyName][$index];
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
     *
     * @param ModelInterface $model
     *
     * @return array|null
     * @throws Exception
     */
    final public function readMetaData(ModelInterface $model): array | null
    {
        $key = $this->getMetaDataUniqueKey($model);
        if ($key !== null) {
            return $this->metaData[$key];
        }

        return null;
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
     *
     * @param ModelInterface $model
     * @param int            $index
     *
     * @return mixed
     * @throws Exception
     * @todo check the return type; 8 seems to be only string
     *
     */
    final public function readMetaDataIndex(ModelInterface $model, int $index): mixed
    {
        $key = $this->getMetaDataUniqueKey($model);
        if ($key !== null) {
            return $this->metaData[$key][$index];
        }

        return null;
    }

    /**
     * Resets internal meta-data in order to regenerate it
     *
     *```php
     * $metaData->reset();
     *```
     *
     * @return void
     */
    public function reset(): void
    {
        $this->metaData  = [];
        $this->columnMap = [];
    }

    /**
     * Set the attributes that must be ignored from the INSERT SQL generation
     *
     *```php
     * $metaData->setAutomaticCreateAttributes(
     *     new Robots(),
     *     [
     *         "created_at" => true,
     *     ]
     * );
     *```
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return void
     * @throws Exception
     */
    public function setAutomaticCreateAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex($model, self::MODELS_AUTOMATIC_DEFAULT_INSERT, $attributes);
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
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return void
     * @throws Exception
     */
    public function setAutomaticUpdateAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex($model, self::MODELS_AUTOMATIC_DEFAULT_UPDATE, $attributes);
    }

    /**
     * Initialize old behaviour for compatability
     *
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
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return void
     * @throws Exception
     */
    public function setEmptyStringAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex(
            $model,
            self::MODELS_EMPTY_STRING_VALUES,
            $attributes
        );
    }

    /**
     * Set the meta-data extraction strategy
     *
     * @param StrategyInterface $strategy
     *
     * @return void
     */
    public function setStrategy(StrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Writes the metadata to adapter
     *
     * @param string $key
     * @param array  $data
     *
     * @return void
     * @throws Exception
     */
    public function write(string $key, array $data): void
    {
        $option = Settings::get("orm.exception_on_failed_metadata_save");
        try {
            $result = $this->adapter->set($key, $data);
            if (false === $result) {
                $this->throwWriteException($option);
            }
        } catch (\Exception) {
            $this->throwWriteException($option);
        }
    }

    /**
     * Writes meta-data for certain model using a MODEL_* constant
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
     *
     * @param ModelInterface $model
     * @param int            $index
     * @param mixed          $data
     *
     * @return void
     * @throws Exception
     */
    final public function writeMetaDataIndex(
        ModelInterface $model,
        int $index,
        mixed $data
    ): void {
        $key = $this->getMetaDataUniqueKey($model);

        if ($key !== null) {
            $this->metaData[$key][$index] = $data;
        }
    }

    /**
     * @param ModelInterface $model
     * @param                $key
     * @param                $table
     * @param                $schema
     *
     * @return void
     * @throws Exception
     */
    final protected function initialize(ModelInterface $model, string $key, $table, $schema): void
    {
        $this->initializeMetaData($model, $key);
        $this->initializeColumnMap($model, $key);
    }

    /**
     * Initialize ColumnMap for a certain table
     *
     * @param ModelInterface $model
     * @param                $key
     *
     * @return bool
     * @throws Exception
     */
    final protected function initializeColumnMap(ModelInterface $model, $key): bool
    {
        if ($key === null) {
            return false;
        }

        /**
         * Check for a column map, store in columnMap in order and reversed order
         */
        if (false === Settings::get("orm.column_renaming")) {
            return false;
        }

        if (isset($this->columnMap[$key])) {
            return true;
        }

        /**
         * Create the map key name
         * Check if the meta-data is already in the adapter
         */
        $prefixKey = 'map-' . $key;
        $data      = $this->read($prefixKey);

        if ($data !== null) {
            $this->columnMap[$key] = $data;

            return true;
        }

        /**
         * Get the meta-data extraction strategy
         */
        $container = $this->getDI();
        $strategy  = $this->getStrategy();

        /**
         * Get the meta-data
         * Update the column map locally
         */
        $modelColumnMap        = $strategy->getColumnMaps($model, $container);
        $this->columnMap[$key] = $modelColumnMap;

        /**
         * Write the data to the adapter
         */
        $this->write($prefixKey, $modelColumnMap);

        return true;
    }

    /**
     * Initialize the metadata for certain table
     *
     * @param ModelInterface $model
     * @param string|null    $key
     *
     * @return bool
     * @throws Exception
     */
    final protected function initializeMetaData(ModelInterface $model, string | null $key): bool
    {
        if ($key !== null) {
            if (false === isset($this->metaData[$key])) {
                /**
                 * The meta-data is read from the adapter always if not available in metaData property
                 */
                $prefixKey = "meta-" . $key;
                $data      = $this->read($prefixKey);

                if ($data !== null) {
                    $this->metaData[$key] = $data;
                } else {
                    /**
                     * Check if there is a method "metaData" in the model to retrieve meta-data from it
                     */
                    if (method_exists($model, "metaData")) {
                        $modelMetadata = $model->metaData();

                        if (!is_array($modelMetadata)) {
                            throw new Exception(
                                "Invalid meta-data for model " . get_class($model)
                            );
                        }
                    } else {
                        /**
                         * Get the meta-data extraction strategy
                         */
                        $container     = $this->getDI();
                        $strategy      = $this->getStrategy();
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
                    $this->write($prefixKey, $modelMetadata);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Throws an exception when the metadata cannot be written
     *
     * @param bool $option
     *
     * @return void
     * @throws Exception
     */
    private function throwWriteException(bool $option): void
    {
        $message = "Failed to store metaData to the cache adapter";

        if ($option) {
            throw new Exception($message);
        } else {
            trigger_error($message);
        }
    }
}
