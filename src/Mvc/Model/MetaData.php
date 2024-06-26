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
use Phalcon\Support\Traits\IniTrait;

use function call_user_func;
use function is_array;
use function method_exists;

/**
 * Phalcon\Mvc\Model\MetaData
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
abstract class MetaData extends Injectable implements MetaDataInterface
{
    use IniTrait;

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
    protected ?CacheAdapterInterface $adapter = null;

    /**
     * @var array
     */
    protected array $columnMap = [];

    /**
     * @var DiInterface|null
     */
    protected ?DiInterface $container = null;

    /**
     * @var array
     */
    protected array $metaData = [];

    /**
     * @var StrategyInterface|null
     */
    protected ?StrategyInterface $strategy = null;

    /**
     * Return the internal cache adapter
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
     */
    public function getAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, MetaData::MODELS_ATTRIBUTES);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
    }

    /**
     * Returns attributes that must be ignored from the INSERT SQL generation
     *
     *```php
     * print_r(eadColumnMapIndex)
     *     )
     * );
     *```
     */
    public function getAutomaticCreateAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getAutomaticUpdateAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getBindTypes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, MetaData::MODELS_DATA_TYPES_BIND);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getColumnMap(ModelInterface $model): array | null
    {
        $data = $this->readColumnMapIndex($model, self::MODELS_COLUMN_MAP);
        if (true === is_array($data) || null === $data) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
    }

    /**
     * Returns a ColumnMap Unique key for meta-data is created using className
     *
     * @return string
     */
    final public function getColumnMapUniqueKey(ModelInterface $model): string | null
    {
        $key = get_class($model);
        if (false === isset($this->columnMap[$key])) {
            if (false === $this->initializeColumnMap($model, $key)) {
                return null;
            }
        }
        return $key;
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
    public function getDataTypes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getDataTypesNumeric(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DATA_TYPES_NUMERIC);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getDefaultValues(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_DEFAULT_VALUES);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getEmptyStringAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_EMPTY_STRING_VALUES);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getIdentityField(ModelInterface $model): string | null
    {
        return $this->readMetaDataIndex($model, self::MODELS_IDENTITY_COLUMN);
    }

    /**
     * Returns a MetaData Unique key for meta-data is created using className
     *
     * @return string
     */
    final public function getMetaDataUniqueKey(ModelInterface $model): string | null
    {
        $key = get_class($model);
        if (false === isset($this->metaData[$key])) {
            if (false === $this->initializeMetaData($model, $key)) {
                return null;
            }
        }
        return $key;
    }

    /**
     * Returns the model UniqueID based on model and array row primary key(s) value(s)
     */
    public function getModelUUID(ModelInterface $model, array $row): string | null
    {
        $pks = $this->readMetaDataIndex($model, MetaData::MODELS_PRIMARY_KEY);
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
     */
    public function getNonPrimaryKeyAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_NON_PRIMARY_KEY);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getNotNullAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, self::MODELS_NOT_NULL);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getPrimaryKeyAttributes(ModelInterface $model): array
    {
        $data = $this->readMetaDataIndex($model, MetaData::MODELS_PRIMARY_KEY);
        if (true === is_array($data)) {
            return $data;
        }
        throw new Exception("The meta-data is invalid or is corrupt");
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
    public function getReverseColumnMap(ModelInterface $model): array | null
    {
        $data = $this->readColumnMapIndex($model, MetaData::MODELS_REVERSE_COLUMN_MAP);
        if (true === is_array($data) || null === $data) {
            return $data;
        }

        throw new Exception("The meta-data is invalid or is corrupt");
    }

    /**
     * Return the strategy to obtain the meta-data
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
     */
    public function hasAttribute(ModelInterface $model, string $attribute): bool
    {
        $columnMap = $this->getReverseColumnMap($model);
        if (true === is_array($columnMap)) {
            return isset($columnMap[$attribute]);
        }
        return isset($this->readMetaData($model)[MetaData::MODELS_DATA_TYPES][$attribute]);
    }


    public function isEmpty(): bool
    {
        return empty($this->metaData);
    }

    /**
     * Compares if two models are the same in memory
     */
    public function modelEquals(ModelInterface $first, ModelInterface $other): bool
    {
        return spl_object_id($first) === spl_object_id($other);
    }

    /**
     * Reads metadata from the adapter
     */
    public function read(?string $key): array | null
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
    final public function readColumnMap(ModelInterface $model): array | null
    {
        if ($this->iniGetBool("orm.column_renaming")) {
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
     */
    final public function readColumnMapIndex(ModelInterface $model, int $index): array | null
    {
        if ($this->iniGetBool("orm.column_renaming")) {
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
     */
    final public function readMetaDataIndex(ModelInterface $model, int $index): array | null
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
     */
    public function setAutomaticCreateAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex($model, MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT, $attributes);
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
    public function setAutomaticUpdateAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex($model, MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE, $attributes);
    }

    /**
     * Initialize old behaviour for compatability
     */
    // TODO: check compatability
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
    public function setEmptyStringAttributes(ModelInterface $model, array $attributes): void
    {
        $this->writeMetaDataIndex(
            $model,
            MetaData::MODELS_EMPTY_STRING_VALUES,
            $attributes
        );
    }

    /**
     * Set the meta-data extraction strategy
     */
    public function setStrategy(StrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Writes the metadata to adapter
     */
    public function write(string $key, array $data): void
    {
        try {
            $option = $this->iniGetBool("orm.exception_on_failed_metadata_save");
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
    final protected function initialize(ModelInterface $model, $key, $table, $schema)
    {
        $this->initializeMetaData($model, $key);
        $this->initializeColumnMap($model, $key);
    }

    /**
     * Initialize ColumnMap for a certain table
     */
    final protected function initializeColumnMap(ModelInterface $model, $key): bool
    {
        if ($key === null) {
            return false;
        }

        /**
         * Check for a column map, store in columnMap in order and reversed order
         */
        if (false === $this->iniGetBool("orm.column_renaming")) {
            return false;
        }

        if (true === isset($this->columnMap[$key])) {
            return true;
        }

        /**
         * Create the map key name
         * Check if the meta-data is already in the adapter
         */
        $prefixKey = 'map-' . $key;
        $data      = $this->{'read'}($prefixKey);

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
        $this->{'write'}($prefixKey, $modelColumnMap);
        return true;
    }

    /**
     * Initialize the metadata for certain table
     */
    final protected function initializeMetaData(ModelInterface $model, $key): bool
    {
        $prefixKey = "";

        $strategy = null;

        if ($key !== null) {
            $metaData = $this->metaData;

            if (false === isset($metaData[$key])) {
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
                        $modelMetadata = call_user_func([$model, "metaData"]);

                        if (false === is_array($modelMetadata)) {
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
                    $this->{"write"}($prefixKey, $modelMetadata);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Throws an exception when the metadata cannot be written
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
