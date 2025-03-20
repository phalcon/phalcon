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

use Phalcon\Annotations\Parser\Collection;
use Phalcon\Db\Column;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\ModelInterface;

class Annotations implements StrategyInterface
{
    /**
     * @param ModelInterface $model
     * @param DiInterface    $container
     *
     * @return array
     * @throws \Phalcon\Annotations\Parser\Exception
     * @throws Exception
     */
    public function getColumnMaps(ModelInterface $model, DiInterface $container): array
    {
        $propertiesAnnotations = $this->getProperties($model, $container);

        $orderedColumnMap  = [];
        $reversedColumnMap = [];
        $hasReversedColumn = false;

        foreach ($propertiesAnnotations as $property => $propAnnotations) {
            /**
             * All columns marked with the "Column" annotation are considered columns
             */
            if (false === $propAnnotations->has("Column")) {
                continue;
            }

            /**
             * Fetch the "column" annotation
             */
            $columnAnnotations = $propAnnotations->get("Column");

            /**
             * Check if annotation has the "column" named parameter
             */
            $columnName = $columnAnnotations->getNamedParameter("column");
            if (empty($columnName)) {
                $columnName = $property;
            }

            $orderedColumnMap[$columnName] = $property;
            $reversedColumnMap[$property]  = $columnName;

            if (false === $hasReversedColumn && $columnName !== $property) {
                $hasReversedColumn = true;
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
     * @return array
     * @throws \Phalcon\Annotations\Parser\Exception
     * @throws Exception
     */
    public function getMetaData(ModelInterface $model, DiInterface $container): array
    {
        $propertiesAnnotations = $this->getProperties($model, $container);

        /**
         * Initialize meta-data
         */
        $annotations       = [];
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

        foreach ($propertiesAnnotations as $property => $propAnnotations) {
            /**
             * All columns marked with the "Column" annotation are considered
             * columns
             */
            if (false === $propAnnotations->has("Column")) {
                continue;
            }

            /**
             * Fetch the "column" annotation
             */
            $columnAnnotations = $propAnnotations->get("Column");

            /**
             * Check if annotation has the "column" named parameter
             */
            $columnName = $columnAnnotations->getNamedParameter("column");

            if (empty($columnName)) {
                $columnName = $property;
            }

            /**
             * Check if annotation has the "type" named parameter
             */
            $feature = $columnAnnotations->getNamedParameter("type");

            $fieldTypes[$columnName] = match ($feature) {
                "biginteger" => Column::TYPE_BIGINTEGER,
                "bit"        => Column::TYPE_BIT,
                "blob"       => Column::TYPE_BLOB,
                "boolean"    => Column::TYPE_BOOLEAN,
                "char"       => Column::TYPE_CHAR,
                "date"       => Column::TYPE_DATE,
                "datetime"   => Column::TYPE_DATETIME,
                "decimal"    => Column::TYPE_DECIMAL,
                "double"     => Column::TYPE_DOUBLE,
                "enum"       => Column::TYPE_ENUM,
                "float"      => Column::TYPE_FLOAT,
                "integer"    => Column::TYPE_INTEGER,
                "json"       => Column::TYPE_JSON,
                "jsonb"      => Column::TYPE_JSONB,
                "longblob"   => Column::TYPE_LONGBLOB,
                "longtext"   => Column::TYPE_LONGTEXT,
                "mediumblob" => Column::TYPE_MEDIUMBLOB,
                "mediumint"  => Column::TYPE_MEDIUMINTEGER,
                "mediumtext" => Column::TYPE_MEDIUMTEXT,
                "smallint"   => Column::TYPE_SMALLINTEGER,
                "text"       => Column::TYPE_TEXT,
                "time"       => Column::TYPE_TIME,
                "timestamp"  => Column::TYPE_TIMESTAMP,
                "tinyblob"   => Column::TYPE_TINYBLOB,
                "tinyint"    => Column::TYPE_TINYINTEGER,
                "tinytext"   => Column::TYPE_TINYTEXT,
                default      => Column::TYPE_VARCHAR,
            };

            $fieldBindTypes[$columnName] = match ($feature) {
                "decimal",
                "double",
                "float"    => Column::BIND_PARAM_DECIMAL,
                "blob",
                "mediumblob",
                "longblob",
                "tinyblob" => Column::BIND_PARAM_BLOB,
                "boolean"  => Column::BIND_PARAM_BOOL,
                "mediumint",
                "smallint",
                "tinyint",
                "bit",
                "integer"  => Column::BIND_PARAM_INT,
                default    => Column::BIND_PARAM_STR,
            };

            $numericTyped[$columnName] = match ($feature) {
                "biginteger",
                "bit",
                "decimal",
                "double",
                "enum",
                "float",
                "integer",
                "mediumint",
                "smallint",
                "tinyint" => true,
                default   => false,
            };

            /**
             * All columns marked with the "Primary" annotation are considered
             * primary keys
             */
            if (true === $propAnnotations->has("Primary")) {
                $primaryKeys[] = $columnName;
            } else {
                $nonPrimaryKeys[] = $columnName;
            }

            /**
             * All columns marked with the "Identity" annotation are considered
             * the column identity
             */
            if (true === $propAnnotations->has("Identity")) {
                $identityField = $columnName;
            }

            /**
             * Column will be skipped on INSERT operation
             */
            if ($columnAnnotations->getNamedParameter("skipOnInsert")) {
                $skipOnInsert[$columnName] = true;
            }

            /**
             * Column will be skipped on UPDATE operation
             */
            if ($columnAnnotations->getNamedParameter("skipOnUpdate")) {
                $skipOnUpdate[$columnName] = true;
            }

            /**
             * Allow empty strings for column
             */
            if ($columnAnnotations->getNamedParameter("allowEmptyString")) {
                $emptyStringValues[$columnName] = true;
            }

            /**
             * Check if the column is nullable
             */
            if (!$columnAnnotations->getNamedParameter("nullable")) {
                $notNull[] = $columnName;
            }

            /**
             * If column has default value or column is nullable and default
             * value is null
             */
            $defaultValue = $columnAnnotations->getNamedParameter("default");
            if ($defaultValue !== null || $columnAnnotations->getNamedParameter("nullable")) {
                $defaultValues[$columnName] = $defaultValue;
            }

            $annotations[] = $columnName;
        }

        /**
         * Create an array using the MODELS_* constants as indexes
         */
        return [
            MetaData::MODELS_ATTRIBUTES               => $annotations,
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
     * @return Collection[]
     * @throws Exception
     */
    private function getProperties(ModelInterface $model, DiInterface $container): array
    {
        if (false === is_object($container)) {
            throw new Exception("The dependency injector is invalid in MetaData Strategy Annotations");
        }

        /** @var \Phalcon\Annotations\Annotations $annotations */
        $annotations = $container->get("annotations");

        $className  = get_class($model);
        $reflection = $annotations->get($className);

        if (!is_object($reflection)) {
            throw new Exception(
                "No annotations were found in class " . $className
            );
        }

        /**
         * Get the properties defined in
         */
        $propertiesAnnotations = $reflection->getPropertiesAnnotations();

        if (0 === count($propertiesAnnotations)) {
            throw new Exception(
                "No properties with annotations were found in class " . $className
            );
        }

        return $propertiesAnnotations;
    }
}
