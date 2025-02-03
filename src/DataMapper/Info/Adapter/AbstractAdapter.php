<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Info
 * @license https://github.com/atlasphp/Atlas.Info/blob/2.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Info\Adapter;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Exception\Exception;

use function explode;
use function in_array;
use function str_getcsv;
use function stripos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;

/**
 * @phpstan-import-type ColumnDefinitionSql from AdapterInterface
 * @phpstan-import-type ColumnDefinition from AdapterInterface
 */
abstract class AbstractAdapter implements AdapterInterface
{
    protected string $currentSchemaSql = '';

    /**
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection
    ) {
    }

    /**
     * Returns the autoincrement or sequence
     *
     * @param string $schema
     * @param string $table
     *
     * @return string|null
     */
    public function getAutoincSequence(string $schema, string $table): ?string
    {
        return null;
    }

    /**
     * Return the current schema name
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentSchema(): string
    {
        /** @var string $currentSchema */
        $currentSchema = $this->connection->fetchValue($this->currentSchemaSql);

        return $currentSchema;
    }

    /**
     * Return the columns in an array with their respective properties
     *
     * @param string $schema
     * @param string $table
     *
     * @return array<string, ColumnDefinition>
     * @throws Exception
     */
    public function listColumns(string $schema, string $table): array
    {
        $autoInc  = $this->getAutoIncSql();
        $extended = $this->getExtendedSql();

        $statement = "
            SELECT
                c.COLUMN_NAME AS name,
                c.DATA_TYPE AS type,
                IF(
                    c.COLUMN_DEFAULT = \"''\" AND
                    (
                        LOCATE('char', c.DATA_TYPE) > 0 OR
                        LOCATE('text', c.DATA_TYPE) > 0
                        ),
                    \"\",
                    IF (
                        LOCATE('CURRENT_TIMESTAMP', c.COLUMN_DEFAULT) > 0,
                        NULL,
                        IF (c.COLUMN_DEFAULT = 'NULL', NULL, c.COLUMN_DEFAULT)
                    )
                ) AS default_value,
                CASE c.DATA_TYPE
                    WHEN 'bigint' THEN 1
                    WHEN 'decimal' THEN 1
                    WHEN 'float' THEN 1
                    WHEN 'int' THEN 1
                    WHEN 'mediumint' THEN 1
                    WHEN 'smallint' THEN 1
                    WHEN 'tinyint' THEN 1
                    ELSE 0
                END AS is_numeric,
                COALESCE(
                    c.CHARACTER_MAXIMUM_LENGTH,
                    c.NUMERIC_PRECISION
                ) AS size,
                c.NUMERIC_SCALE AS numeric_scale,
                IF(c.is_nullable = 'YES', 0, 1) AS is_not_null,
                c.COLUMN_COMMENT AS comment,
                IF(c.ordinal_position = 1, 1, 0) AS is_first,
                IF(tc.constraint_type = 'PRIMARY KEY', 1, 0) AS is_primary,
                IF(
                    LOCATE('int', c.COLUMN_TYPE) > 0,
                    IF(LOCATE('unsigned', c.COLUMN_TYPE) > 0, 1, 0),
                    NULL
                ) AS is_unsigned,
                $autoInc AS is_auto_increment,
                $extended AS extended
            
            FROM information_schema.columns c
            LEFT JOIN information_schema.key_column_usage kcu
                ON  c.TABLE_SCHEMA = kcu.TABLE_SCHEMA
                    AND c.TABLE_NAME = kcu.TABLE_NAME
                    AND c.COLUMN_NAME = kcu.COLUMN_NAME
            LEFT JOIN information_schema.table_constraints tc
                ON  kcu.TABLE_SCHEMA = tc.TABLE_SCHEMA
                    AND kcu.TABLE_NAME = tc.TABLE_NAME
                    AND kcu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
            WHERE c.TABLE_SCHEMA = :schema
              AND c.TABLE_NAME = :table
            ORDER BY c.ORDINAL_POSITION
        ";

        /** @var ColumnDefinitionSql[] $columns */
        $columns = $this->connection->fetchAll(
            $statement,
            [
                'schema' => $schema,
                'table'  => $table,
            ]
        );

        return $this->transformColumns($columns);
    }

    /**
     * Return an array with the schema and table name
     *
     * @param string $schemaTable
     *
     * @return string[]
     * @throws Exception
     */
    public function listSchemaTable(string $schemaTable): array
    {
        $parts = explode('.', $schemaTable, 2);

        if (count($parts) === 1) {
            return [
                $this->getCurrentSchema(),
                $schemaTable,
            ];
        }

        return $parts;
    }

    /**
     * Returns the SQL for the auto increment column
     *
     * @return string
     */
    protected function getAutoIncSql(): string
    {
        return "''";
    }

    /**
     * Returns the actual default value of a column
     *
     * @param mixed  $defaultValue
     * @param string $type
     *
     * @return mixed
     */
    protected function getDefault(
        mixed $defaultValue,
        string $type
    ): mixed {
        return $defaultValue;
    }

    /**
     * Returns the SQL for the extended field (MySQL)
     *
     * @return string
     */
    protected function getExtendedSql(): string
    {
        return "''";
    }

    /**
     * @param ColumnDefinitionSql $column
     *
     * @return ColumnDefinition
     */
    protected function processColumn(array $column): array
    {
        $result = [
            'afterField'      => null,
            'comment'         => $column['comment'],
            'default'         => $this->processDefault(
                $column['default_value'],
                $column['type']
            ),
            'hasDefault'      => null !== $column['default_value'],
            'isAutoIncrement' => (bool)$column['is_auto_increment'],
            'isFirst'         => (bool)$column['is_first'],
            'isNotNull'       => (bool)$column['is_not_null'],
            'isNumeric'       => (bool)$column['is_numeric'],
            'isPrimary'       => (bool)$column['is_primary'],
            'isUnsigned'      => null !== $column['is_unsigned']
                ? (bool)$column['is_unsigned']
                : null,
            'name'            => $column['name'],
            'options'         => null,
            'scale'           => isset($column['numeric_scale'])
                ? (int)$column['numeric_scale']
                : null,
            'size'            => isset($column['size'])
                ? (int)$column['size']
                : null,
            'type'            => $column['type'],
        ];

        $extended = trim($column['extended']);

        /**
         * Enum
         */
        if (stripos($extended, 'enum') === 0) {
            $input             = trim(substr($extended, 4), '()');
            $result['options'] = str_getcsv($input);
        }

        return $result;
    }

    /**
     * Process the default value based on the type and return the correct type
     * back
     *
     * @param mixed  $defaultValue
     * @param string $type
     *
     * @return mixed
     */
    protected function processDefault(mixed $defaultValue, string $type): mixed
    {
        $type         = strtolower($type);
        $defaultValue = $this->getDefault($defaultValue, $type);
        $charTypes    = ['char', 'text', 'varchar'];
        $floatTypes   = ['decimal', 'double', 'float', 'numeric', 'real'];
        $keywordTypes = ['CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP'];

        if (
            null === $defaultValue ||
            true === in_array(strtoupper((string)$defaultValue), $keywordTypes)
        ) {
            return null;
        }
        if (
            true === in_array($type, $charTypes) &&
            "''" === $defaultValue
        ) {
            return '';
        }

        return match (true) {
            str_contains($type, 'int')   => (int)$defaultValue,
            in_array($type, $floatTypes) => (float)$defaultValue,
            default                      => $defaultValue,
        };
    }

    /**
     * @param string $value
     * @param string $type
     *
     * @return bool|null
     */
    /**
     * @param ColumnDefinition $columns
     *
     * @return ColumnDefinition
     */
    protected function transformColumns(array $columns): array
    {
        $results  = [];
        $previous = null;
        $first    = reset($columns);
        $firstName = $first['name'];
        foreach ($columns as $column) {
            $name = $column['name'];
            if ($name === $firstName) {
                $column['isFirst'] = true;
            }

            $results[$name] = $this->processColumn($column);
            $results[$name]['afterField'] = $previous;
            $previous = $name;
        }

        return $results;
    }
}
