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

use Phalcon\Mvc\Model\MetaData\Strategy\StrategyInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\MetaDataInterface
 *
 * Interface for Phalcon\Mvc\Model\MetaData
 */
interface MetaDataInterface
{
    /**
     * Returns table attributes names (fields)
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getAttributes(ModelInterface $model): array;

    /**
     * Returns attributes that must be ignored from the INSERT SQL generation
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getAutomaticCreateAttributes(ModelInterface $model): array;

    /**
     * Returns attributes that must be ignored from the UPDATE SQL generation
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getAutomaticUpdateAttributes(ModelInterface $model): array;

    /**
     * Returns attributes and their bind data types
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getBindTypes(ModelInterface $model): array;

    /**
     * Returns the column map if any
     *
     * @param ModelInterface $model
     *
     * @return array|null
     */
    public function getColumnMap(ModelInterface $model): array | null;

    /**
     * Returns attributes and their data types
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getDataTypes(ModelInterface $model): array;

    /**
     * Returns attributes which types are numerical
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getDataTypesNumeric(ModelInterface $model): array;

    /**
     * Returns attributes (which have default values) and their default values
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getDefaultValues(ModelInterface $model): array;

    /**
     * Returns attributes allow empty strings
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getEmptyStringAttributes(ModelInterface $model): array;

    /**
     * Returns the name of identity field (if one is present)
     *
     * @param ModelInterface $model
     *
     * @return bool|string|null
     */
    public function getIdentityField(ModelInterface $model): bool | string | null;

    /**
     * Returns an array of fields which are not part of the primary key
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getNonPrimaryKeyAttributes(ModelInterface $model): array;

    /**
     * Returns an array of not null attributes
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getNotNullAttributes(ModelInterface $model): array;

    /**
     * Returns an array of fields which are part of the primary key
     *
     * @param ModelInterface $model
     *
     * @return array
     */
    public function getPrimaryKeyAttributes(ModelInterface $model): array;

    /**
     * Returns the reverse column map if any
     *
     * @param ModelInterface $model
     *
     * @return array|null
     */
    public function getReverseColumnMap(ModelInterface $model): array | null;

    /**
     * Return the strategy to obtain the meta-data
     *
     * @return StrategyInterface
     */
    public function getStrategy(): StrategyInterface;

    /**
     * Check if a model has certain attribute
     *
     * @param ModelInterface $model
     * @param string         $attribute
     *
     * @return bool
     */
    public function hasAttribute(ModelInterface $model, string $attribute): bool;

    /**
     * Checks if the internal meta-data container is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Reads meta-data from the adapter
     *
     * @param string $key
     *
     * @return array|null
     */
    public function read(string $key): array | null;

    /**
     * Reads the ordered/reversed column map for certain model
     *
     * @param ModelInterface $model
     *
     * @return array|null
     */
    public function readColumnMap(ModelInterface $model): array | null;

    /**
     * Reads column-map information for certain model using a MODEL_* constant
     *
     * @param ModelInterface $model
     * @param int            $index
     *
     * @return mixed
     */
    public function readColumnMapIndex(ModelInterface $model, int $index);

    /**
     * Reads meta-data for certain model
     *
     * @param ModelInterface $model
     *
     * @return array | null
     */
    public function readMetaData(ModelInterface $model): array | null;

    /**
     * Reads meta-data for certain model using a MODEL_* constant
     *
     * @param ModelInterface $model
     * @param int            $index
     *
     * @return mixed
     */
    public function readMetaDataIndex(ModelInterface $model, int $index): mixed;

    /**
     * Resets internal meta-data in order to regenerate it
     *
     * @return mixed
     */
    public function reset();

    /**
     * Set the attributes that must be ignored from the INSERT SQL generation
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return mixed
     */
    public function setAutomaticCreateAttributes(
        ModelInterface $model,
        array $attributes
    );

    /**
     * Set the attributes that must be ignored from the UPDATE SQL generation
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return mixed
     */
    public function setAutomaticUpdateAttributes(
        ModelInterface $model,
        array $attributes
    );

    /**
     * Set the attributes that allow empty string values
     *
     * @param ModelInterface $model
     * @param array          $attributes
     *
     * @return void
     */
    public function setEmptyStringAttributes(
        ModelInterface $model,
        array $attributes
    ): void;

    /**
     * Set the meta-data extraction strategy
     *
     * @param StrategyInterface $strategy
     *
     * @return mixed
     */
    public function setStrategy(StrategyInterface $strategy);

    /**
     * Writes meta-data to the adapter
     *
     * @param string $key
     * @param array  $data
     *
     * @return void
     */
    public function write(string $key, array $data): void;

    /**
     * Writes meta-data for certain model using a MODEL_* constant
     *
     * @param ModelInterface $model
     * @param int            $index
     * @param mixed          $data
     *
     * @return mixed
     */
    public function writeMetaDataIndex(
        ModelInterface $model,
        int $index,
        mixed $data
    );
}
