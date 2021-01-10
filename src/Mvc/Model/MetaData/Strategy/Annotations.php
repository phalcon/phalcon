<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\Model\MetaData\Strategy;

use Phiz\Di\DiInterface;
use Phiz\Db\Column;
use Phiz\Mvc\ModelInterface;
use Phiz\Mvc\Model\MetaData;
use Phiz\Mvc\Model\Exception;

class Annotations implements StrategyInterface
{
    /**
     * Read the model's column map, this can't be inferred
     */
    final public function getColumnMaps(ModelInterface $model , DiInterface $container ) : array
    {
        if (!is_object($container)) {
            throw new Exception("The dependency injector is invalid");
        }

       $annotations = $container->get("annotations");

       $className = get_class($model);
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

        if (!count($propertiesAnnotations)) {
            throw new Exception(
                "No properties with annotations were found in class " . $className
            );
        }

       $orderedColumnMap = [];
            $reversedColumnMap = [];
            $hasReversedColumn = false;

        foreach ($propertiesAnnotations as $property => $propAnnotations)  {
            /**
             * All columns marked with the 'Column' annotation are considered columns
             */
            if (!$propAnnotations->has("Column")) {
                continue;
            }

            /**
             * Fetch the 'column' annotation
             */
           $columnAnnotation = $propAnnotations->get("Column");

            /**
             * Check if annotation has the 'column' named parameter
             */
           $columnName = $columnAnnotation->getNamedParameter("column");

            if (empty($columnName)) {
               $columnName = $property;
            }

           $orderedColumnMap[$columnName] = $property;
           $reversedColumnMap[$property] = $columnName;

            if (!$hasReversedColumn && $columnName != $property) {
               $hasReversedColumn = true;
            }
        }

        if (!$hasReversedColumn) {
            return [null, null];
        }

        /**
         * Store the column map
         */
        return [$orderedColumnMap, $reversedColumnMap];
    }

    /**
     * The meta-data is obtained by reading the column descriptions from the database information schema
     */
    final public function getMetaData(ModelInterface $model , DiInterface $container ) : array
    {
        if (!is_object($container)) {
            throw new Exception("The dependency injector is invalid");
        }

       $annotations = $container->get("annotations");

       $className = get_class($model);
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

        if (!count($propertiesAnnotations)) {
            throw new Exception(
                "No properties with annotations were found in class " . $className
            );
        }

        /**
         * Initialize meta-data
         */
       $attributes = [];
            $primaryKeys = [];
            $nonPrimaryKeys = [];
            $numericTyped = [];
            $notNull = [];
            $fieldTypes = [];
            $fieldBindTypes = [];
            $identityField = false;
            $skipOnInsert = [];
            $skipOnUpdate = [];
            $defaultValues = [];
            $emptyStringValues = [];

        foreach ($propertiesAnnotations as $property => $propAnnotations ) {
            /**
             * All columns marked with the 'Column' annotation are considered
             * columns
             */
            if (!$propAnnotations->has("Column")) {
                continue;
            }

            /**
             * Fetch the 'column' annotation
             */
           $columnAnnotation = $propAnnotations->get("Column");

            /**
             * Check if annotation has the 'column' named parameter
             */
           $columnName = $columnAnnotation->getNamedParameter("column");

            if (empty ($columnName)) {
               $columnName = $property;
            }

            /**
             * Check if annotation has the 'type' named parameter
             */
           $feature = $columnAnnotation->getNamedParameter("type");

            switch ($feature) {
                case "biginteger":
                   $fieldTypes[$columnName] = Column::TYPE_BIGINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "bit":
                   $fieldTypes[$columnName] = Column::TYPE_BIT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "blob":
                   $fieldTypes[$columnName] = Column::TYPE_BLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                    break;

                case "boolean":
                   $fieldTypes[$columnName] = Column::TYPE_BOOLEAN;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BOOL;
                    break;

                case "char":
                   $fieldTypes[$columnName] = Column::TYPE_CHAR;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "date":
                   $fieldTypes[$columnName] = Column::TYPE_DATE;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "datetime":
                   $fieldTypes[$columnName] = Column::TYPE_DATETIME;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "decimal":
                   $fieldTypes[$columnName] = Column::TYPE_DECIMAL;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName] = true;
                    break;

                case "double":
                   $fieldTypes[$columnName] = Column::TYPE_DOUBLE;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName] = true;
                    break;

                case "enum":
                   $fieldTypes[$columnName] = Column::TYPE_ENUM;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                        $numericTyped[$columnName] = true;
                    break;

                case "float":
                   $fieldTypes[$columnName] = Column::TYPE_FLOAT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_DECIMAL;
                        $numericTyped[$columnName] = true;
                    break;

                case "integer":
                   $fieldTypes[$columnName] = Column::TYPE_INTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "json":
                   $fieldTypes[$columnName] = Column::TYPE_JSON;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "jsonb":
                   $fieldTypes[$columnName] = Column::TYPE_JSONB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "longblob":
                   $fieldTypes[$columnName] = Column::TYPE_LONGBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                    break;

                case "longtext":
                   $fieldTypes[$columnName] = Column::TYPE_LONGTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "mediumblob":
                   $fieldTypes[$columnName] = Column::TYPE_MEDIUMBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                    break;

                case "mediumint":
                   $fieldTypes[$columnName] = Column::TYPE_MEDIUMINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "mediumtext":
                   $fieldTypes[$columnName] = Column::TYPE_MEDIUMTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "smallint":
                   $fieldTypes[$columnName] = Column::TYPE_SMALLINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "text":
                   $fieldTypes[$columnName] = Column::TYPE_TEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "time":
                   $fieldTypes[$columnName] = Column::TYPE_TIME;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "timestamp":
                   $fieldTypes[$columnName] = Column::TYPE_TIMESTAMP;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                case "tinyblob":
                   $fieldTypes[$columnName] = Column::TYPE_TINYBLOB;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_BLOB;
                    break;

                case "tinyint":
                   $fieldTypes[$columnName] = Column::TYPE_TINYINTEGER;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_INT;
                        $numericTyped[$columnName] = true;
                    break;

                case "tinytext":
                   $fieldTypes[$columnName] = Column::TYPE_TINYTEXT;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
                    break;

                default:
                    /**
                     * By default all columns are varchar/string
                     */
                   $fieldTypes[$columnName] = Column::TYPE_VARCHAR;
                        $fieldBindTypes[$columnName] = Column::BIND_PARAM_STR;
            }

            /**
             * All columns marked with the 'Primary' annotation are considered
             * primary keys
             */
            if ($propAnnotations->has("Primary")) {
               $primaryKeys[] = $columnName;
            } else {
               $nonPrimaryKeys[] = $columnName;
            }

            /**
             * All columns marked with the 'Identity' annotation are considered
             * the column identity
             */
            if ($propAnnotations->has("Identity")) {
               $identityField = $columnName;
            }

            /**
             * Column will be skipped on INSERT operation
             */
            if ($columnAnnotation->getNamedParameter("skip_on_insert")) {
               $skipOnInsert[] = $columnName;
            }

            /**
             * Column will be skipped on UPDATE operation
             */
            if ($columnAnnotation->getNamedParameter("skip_on_update")){
                
                $skipOnUpdate[] = $columnName;
            }

            /**
             * Allow empty strings for column
             */
            if ($columnAnnotation->getNamedParameter("allow_empty_string")) {
               $emptyStringValues[] = $columnName;
            }

            /**
             * Check if the column is nullable
             */
            if (!$columnAnnotation->getNamedParameter("nullable")) {
               $notNull[] = $columnName;
            }

            /**
             * If column has default value or column is nullable and default
             * value is null
             */
           $defaultValue = $columnAnnotation->getNamedParameter("default");
            if ($defaultValue !== null || $columnAnnotation->getNamedParameter("nullable")) {
               $defaultValues[$columnName] = $defaultValue;
            }

           $attributes[] = $columnName;
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
            MetaData::MODELS_EMPTY_STRING_VALUES      => $emptyStringValues
        ];
    }
}
