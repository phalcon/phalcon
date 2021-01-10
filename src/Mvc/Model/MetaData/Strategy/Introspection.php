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
use Phiz\Db\Adapter\AdapterInterface;
use Phiz\Db\Column;
use Phiz\Mvc\ModelInterface;
use Phiz\Mvc\Model\Exception;
use Phiz\Mvc\Model\MetaData;

/**
 * Phiz\Mvc\Model\MetaData\Strategy\Introspection
 *
 * Queries the table meta-data in order to introspect the model's metadata
 */
class Introspection implements StrategyInterface
{
    /**
     * Read the model's column map, this can't be inferred
     */
    final public function getColumnMaps(ModelInterface $model, DiInterface $container): array
    {
       $orderedColumnMap = null;
       $reversedColumnMap = null;

        /**
         * Check for a columnMap() method on the model
         */
        if (method_exists($model, "columnMap")) {
           $userColumnMap = $model->{"columnMap"}();

            if (!is_array($userColumnMap)) {
                throw new Exception("columnMap() did not return an array");
            }

           $reversedColumnMap = [];
           $orderedColumnMap = $userColumnMap;

            foreach($userColumnMap as $name  => $userName) {
               $reversedColumnMap[$userName] = $name;
            }
        }

        /**
         * Store the column map
         */
        return [$orderedColumnMap, $reversedColumnMap];
    }

    /**
     * The meta-data is obtained by reading the column descriptions from the database information schema
     */
    final public function getMetaData(ModelInterface $model, DiInterface $container): array
    {
       $schema = $model->getSchema();
        $table  = $model->getSource();

        /**
         * Check if the mapped table exists on the database
         */
       $readConnection = $model->getReadConnection();

        if (!$readConnection->tableExists($table, $schema)) {
            if (!empty($schema)) {
               $completeTable = $schema . "'.'" . $table;
            } else {
               $completeTable = $table;
            }

            /**
             * The table not exists
             */
            throw new Exception(
                "Table '" . $completeTable . 
                "' doesn't exist in database when dumping meta-data for " . get_class($model)
            );
        }

        /**
         * Try to describe the table
         */
       $columns = $readConnection->describeColumns($table, $schema);

        if (empty($columns)) {
            if (!empty($schema)) {
               $completeTable = $schema . "'.'" . $table;
            } else {
               $completeTable = $table;
            }

            /**
             * The table not exists
             */
            throw new Exception(
                "Cannot obtain table columns for the mapped source '" . $completeTable . 
                "' used in $model " . get_class($model)
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
       $automaticDefault = [];
       $identityField = false;
       $defaultValues = [];
       $emptyStringValues = [];

        foreach( $columns as $column) {
            $fieldName = $column->getName();
            $attributes[] = $fieldName;

            /**
             * To mark fields as primary keys
             */
            if ($column->isPrimary()) {
               $primaryKeys[] = $fieldName;
            } else {
               $nonPrimaryKeys[] = $fieldName;
            }

            /**
             * To mark fields as numeric
             */
            if ($column->isNumeric()) {
               $numericTyped[$fieldName] = true;
            }

            /**
             * To mark fields as not null
             */
            if ($column->isNotNull()) {
               $notNull[] = $fieldName;
            }

            /**
             * To mark fields as identity columns
             */
            if ($column->isAutoIncrement()) {
               $identityField = $fieldName;
            }

            /**
             * To get the internal types
             */
           $fieldTypes[$fieldName] = $column->getType();

            /**
             * To mark how the fields must be escaped
             */
           $fieldBindTypes[$fieldName] = $column->getBindType();

            /**
             * If column has default value or column is nullable and default value is null
             */
           $defaultValue = $column->getDefault();

            if ($defaultValue !== null || !$column->isNotNull()) {
                if (!$column->isAutoIncrement()) {
                   $defaultValues[$fieldName] = $defaultValue;
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
            MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT => $automaticDefault,
            MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE => $automaticDefault,
            MetaData::MODELS_DEFAULT_VALUES           => $defaultValues,
            MetaData::MODELS_EMPTY_STRING_VALUES      => $emptyStringValues
        ];
    }
}
