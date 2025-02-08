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

namespace Phalcon\Mvc\Model\MetaData\Strategy;

use Phalcon\Components\Attributes\Models\MetaData\Column as MetaDataColumn;
use Phalcon\Components\Attributes\Models\MetaData\Identity;
use Phalcon\Components\Attributes\Models\MetaData\Primary;
use Phalcon\Db\Column;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\ModelInterface;
use ReflectionClass;

class Attributes implements StrategyInterface
{
    /**
     * @param ModelInterface $model
     * @param DiInterface    $container
     *
     * @throws Exception
     * @return array
     */
    public function getColumnMaps(ModelInterface $model, DiInterface $container): array
    {
        $properties = $this->getProperties($model, $container);

        $orderedColumnMap  = [];
        $reversedColumnMap = [];
        $hasReversedColumn = false;

        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                /**
                 * All columns marked with the "Column" annotation are considered columns
                 */
                if ($attribute->getName() !== MetaDataColumn::class) {
                    continue;
                }

                /**
                 * Fetch the "column" annotation
                 */
                $arguments = $attribute->getArguments();

                /**
                 * Check if annotation has the "column" named parameter
                 */
                $columnName = $arguments['column'] ?? null;

                if (true === empty($columnName)) {
                    $columnName = $property->getName();
                }

                $orderedColumnMap[$columnName]           = $property->getName();
                $reversedColumnMap[$property->getName()] = $columnName;

                if (false === $hasReversedColumn && $columnName !== $property->getName()) {
                    $hasReversedColumn = true;
                }
            }
        }

        if (false === $hasReversedColumn) {
            return [null, null];
        }

        /**
         * Store the column map
         */
        return [$orderedColumnMap, $reversedColumnMap];
    }

    /**
     * @param ModelInterface $model
     * @param DiInterface    $container
     *
     * @throws Exception
     * @return array
     */
    public function getMetaData(ModelInterface $model, DiInterface $container): array
    {
        $properties = $this->getProperties($model, $container);

        /**
         * Initialize meta-data
         */
        $attributes        = [];
        $primaryKeys       = [];
        $nonPrimaryKeys    = [];
        $numericTyped      = [];
        $notNull           = [];
        $fieldTypes        = [];
        $fieldBindTypes    = [];
        $identityField     = false;
        $skipOnInsert      = [];
        $skipOnUpdate      = [];
        $defaultValues     = [];
        $emptyStringValues = [];

        foreach ($properties as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ($attribute->getName() !== MetaDataColumn::class) {
                    continue;
                }
                $arguments = $attribute->getArguments();
                /**
                 * Check if annotation has the "column" named parameter
                 */
                $columnName = $arguments['column'] ?? null;

                if (true === empty($columnName)) {
                    $columnName = $property->getName();
                }

                $attributes[] = $columnName;

                /**
                 * Check if annotation has the "type" named parameter
                 */
                switch ($arguments['type']) {
                    case "biginteger":
                        $fieldTypes[$columnName]     = Column::TYPE_BIGINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "bit":
                        $fieldTypes[$columnName]     = Column::TYPE_BIT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "blob":
                        $fieldTypes[$columnName]     = Column::TYPE_BLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                        break;

                    case "boolean":
                        $fieldTypes[$columnName]     = Column::TYPE_BOOLEAN;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BOOL;
                        break;

                    case "char":
                        $fieldTypes[$columnName]     = Column::TYPE_CHAR;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "date":
                        $fieldTypes[$columnName]     = Column::TYPE_DATE;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "datetime":
                        $fieldTypes[$columnName]     = Column::TYPE_DATETIME;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "decimal":
                        $fieldTypes[$columnName]     = Column::TYPE_DECIMAL;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "double":
                        $fieldTypes[$columnName]     = Column::TYPE_DOUBLE;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "enum":
                        $fieldTypes[$columnName]     = Column::TYPE_ENUM;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "float":
                        $fieldTypes[$columnName]     = Column::TYPE_FLOAT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "integer":
                        $fieldTypes[$columnName]     = Column::TYPE_INTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "json":
                        $fieldTypes[$columnName]     = Column::TYPE_JSON;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "jsonb":
                        $fieldTypes[$columnName]     = Column::TYPE_JSONB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "longblob":
                        $fieldTypes[$columnName]     = Column::TYPE_LONGBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                        break;

                    case "longtext":
                        $fieldTypes[$columnName]     = Column::TYPE_LONGTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "mediumblob":
                        $fieldTypes[$columnName]     = Column::TYPE_MEDIUMBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                        break;

                    case "mediumint":
                        $fieldTypes[$columnName]     = Column::TYPE_MEDIUMINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "mediumtext":
                        $fieldTypes[$columnName]     = Column::TYPE_MEDIUMTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "smallint":
                        $fieldTypes[$columnName]     = Column::TYPE_SMALLINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "text":
                        $fieldTypes[$columnName]     = Column::TYPE_TEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "time":
                        $fieldTypes[$columnName]     = Column::TYPE_TIME;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "timestamp":
                        $fieldTypes[$columnName]     = Column::TYPE_TIMESTAMP;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    case "tinyblob":
                        $fieldTypes[$columnName]     = Column::TYPE_TINYBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                        break;

                    case "tinyint":
                        $fieldTypes[$columnName]     = Column::TYPE_TINYINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName]   = true;
                        break;

                    case "tinytext":
                        $fieldTypes[$columnName]     = Column::TYPE_TINYTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        break;

                    default:
                        /**
                         * By default all columns are varchar/string
                         */
                        $fieldTypes[$columnName]     = Column::TYPE_VARCHAR;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                }

                /**
                 * Column will be skipped on INSERT operation
                 */
                if (($arguments['skipOnInsert'] ?? false) !== false) {
                    $skipOnInsert[$columnName] = true;
                }

                /**
                 * Column will be skipped on UPDATE operation
                 */
                if (($arguments['skipOnUpdate'] ?? false) !== false) {
                    $skipOnUpdate[$columnName] = true;
                }

                /**
                 * Allow empty strings for column
                 */
                if (($arguments['allowEmptyString'] ?? false) !== false) {
                    $emptyStringValues[$columnName] = true;
                }

                /**
                 * Check if the column is nullable
                 */
                if (($arguments['nullable'] ?? false) === false) {
                    $notNull[] = $columnName;
                }

                /**
                 * If column has default value or column is nullable and default
                 * value is null
                 */
                $defaultValue = $arguments['default'] ?? null;
                if ($defaultValue !== null || ($arguments['nullable'] ?? false)) {
                    $defaultValues[$columnName] = $defaultValue;
                }

                /**
                 * All columns marked with the "Primary" annotation are considered
                 * primary keys
                 */
                if (
                    count(
                        array_filter(
                            $property->getAttributes(),
                            static fn ($attribute) => $attribute->getName() === Primary::class
                        )
                    ) !== 0
                ) {
                    $primaryKeys[] = $columnName;
                } else {
                    $nonPrimaryKeys[] = $columnName;
                }

                /**
                 * All columns marked with the "Identity" annotation are considered
                 * the column identity
                 */
                if (
                    count(
                        array_filter(
                            $property->getAttributes(),
                            static fn ($attribute) => $attribute->getName() === Identity::class
                        )
                    ) !== 0
                ) {
                    $identityField = $columnName;
                }
            }
        }

        /**
         * Create an array using the MODELS_* constants as indexes
         */
        return [
            MetaData::MODELS_ATTRIBUTES               => $attributes,
            MetaData::MODELS_PRIMARY_KEY              => $primaryKeys,
            MetaData::MODELS_NON_PRIMARY_KEY          => $nonPrimaryKeys,
            MetaData::MODELS_NOT_NULL                 => $notNull,
            MetaData::MODELS_DATA_TYPES               => $fieldTypes,
            MetaData::MODELS_DATA_TYPES_NUMERIC       => $numericTyped,
            MetaData::MODELS_IDENTITY_COLUMN          => $identityField,
            MetaData::MODELS_DATA_TYPES_BIND          => $fieldBindTypes,
            MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT => $skipOnInsert,
            MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE => $skipOnUpdate,
            MetaData::MODELS_DEFAULT_VALUES           => $defaultValues,
            MetaData::MODELS_EMPTY_STRING_VALUES      => $emptyStringValues,
        ];
    }

    /**
     * @param ModelInterface $model
     * @param DiInterface    $container
     *
     * @throws Exception
     * @return array
     */
    private function getProperties(ModelInterface $model, DiInterface $container): array
    {
        if (false === is_object($container)) {
            throw new Exception("The dependency injector is invalid in MetaData Strategy Attributes");
        }

        $className  = get_class($model);
        $reflection = new ReflectionClass($model);

        if (false === is_object($reflection)) {
            throw new Exception(
                "No attributes were found in class " . $className
            );
        }

        /**
         * Get the properties defined in
         */
        $properties = $reflection->getProperties();

        if (0 === count($properties)) {
            throw new Exception(
                "No properties with attributes were found in class " . $className
            );
        }

        return $properties;
    }
}
