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
use function get_class;
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
    public function getAutoincSequence(string $schema, string $table): string | null
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
        $statement = $this->getListColumnSql($schema, $table);

        if (get_class($this) === Sqlite::class) {
            /** @var ColumnDefinitionSql[] $columns */
            $columns = $this->connection->fetchAll($statement);
        } else {
            /** @var ColumnDefinitionSql[] $columns */
            $columns = $this->connection->fetchAll(
                $statement,
                [
                    'schema' => $schema,
                    'table'  => $table,
                ]
            );
        }

        $processed = $this->transformColumns($columns);

        /**
         * Additional processing (default none)
         */
        return $this->processColumnInformation($schema, $table, $processed);
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
     * Returns the SQL for the comment column
     *
     * @return string
     */
    protected function getCommentSql(): string
    {
        return "''";
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
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    protected function getListColumnSql(string $schema, string $table): string
    {
        $autoInc  = $this->getAutoIncSql();
        $comment  = $this->getCommentSql();
        $extended = $this->getExtendedSql();
        $unsigned = $this->getUnsignedSql();

        return "
            SELECT
                c.column_name as name,
                c.data_type as type,
                COALESCE(
                    c.character_maximum_length,
                    c.numeric_precision
                ) AS size,
                c.numeric_scale AS numeric_scale,
                CASE
                    WHEN (
                            c.column_default IS NULL OR
                            c.column_default = 'NULL' OR
                            POSITION('CURRENT_TIMESTAMP' IN c.column_default) > 0 OR
                            (
                                c.column_default = '\"\"' AND
                                (
                                    POSITION('char' IN c.data_type) > 0 OR
                                    POSITION('text' IN c.data_type) > 0
                                )
                            )
                        )
                        THEN NULL
                    ELSE c.column_default
                END AS default_value,
                CASE c.data_type
                    WHEN 'bigint' THEN 1
                    WHEN 'decimal' THEN 1
                    WHEN 'float' THEN 1
                    WHEN 'int' THEN 1
                    WHEN 'integer' THEN 1
                    WHEN 'mediumint' THEN 1
                    WHEN 'numeric' THEN 1
                    WHEN 'real' THEN 1
                    WHEN 'smallint' THEN 1
                    WHEN 'tinyint' THEN 1
                    ELSE 0
                END AS is_numeric,
                CASE c.is_nullable
                    WHEN 'YES'
                    THEN 0
                    ELSE 1
                END AS is_not_null,
                $comment AS comment,
                CASE c.ordinal_position
                    WHEN 1
                    THEN 1
                    ELSE 0
                END AS is_first,
                CASE tc.constraint_type
                    WHEN 'PRIMARY KEY'
                    THEN 1
                    ELSE 0
                END AS is_primary,
                $unsigned AS is_unsigned,
                $autoInc AS is_auto_increment,
                $extended AS extended
            FROM information_schema.columns c
                LEFT JOIN information_schema.key_column_usage kcu
                    ON  c.table_schema = kcu.table_schema
                    AND c.table_name   = kcu.table_name
                    AND c.column_name  = kcu.column_name
                LEFT JOIN information_schema.table_constraints tc
                    ON  kcu.table_schema    = tc.table_schema
                    AND kcu.table_name      = tc.table_name
                    AND kcu.constraint_name = tc.constraint_name
            WHERE c.table_schema = :schema
            AND   c.table_name   = :table
            ORDER BY c.ordinal_position
        ";
    }

    /**
     * Returns the SQL for the unsigned field (MySQL)
     *
     * @return string
     */
    protected function getUnsignedSql(): string
    {
        return 'NULL';
    }

    /**
     * @param ColumnDefinitionSql $column
     *
     * @return ColumnDefinition
     */
    protected function processColumn(array $column): array
    {
        [$defaultValue, $hasDefault] = $this->processDefault(
            $column['default_value'],
            $column['type']
        );

        $result = [
            'afterField'      => null,
            'comment'         => $column['comment'],
            'default'         => $defaultValue,
            'hasDefault'      => $hasDefault,
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
     * @param string                          $schema
     * @param string                          $table
     * @param array<string, ColumnDefinition> $columns
     *
     * @return array<string, ColumnDefinition>
     */
    protected function processColumnInformation(
        string $schema,
        string $table,
        array $columns
    ): array {
        return $columns;
    }

    /**
     * Process the default value based on the type and return the correct type
     * back
     *
     * @param mixed  $defaultValue
     * @param string $type
     *
     * @return array<array-key, bool|mixed>
     */
    protected function processDefault(mixed $defaultValue, string $type): array
    {
        $type         = strtolower($type);
        $charTypes    = ['char', 'text', 'varchar'];
        $floatTypes   = ['decimal', 'double', 'float', 'numeric', 'real'];
        $keywordTypes = ['CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP'];

        if (
            null === $defaultValue ||
            true === in_array(strtoupper((string)$defaultValue), $keywordTypes)
        ) {
            return [null, false];
        }

        if (
            true === in_array($type, $charTypes) &&
            "''" === $defaultValue
        ) {
            return ['', true];
        }

        return match (true) {
            str_contains($type, 'int')   => [(int)$defaultValue, true],
            in_array($type, $floatTypes) => [(float)$defaultValue, true],
            default                      => [$defaultValue, true]
        };
    }

    /**
     * @param ColumnDefinitionSql[] $columns
     *
     * @return ColumnDefinition[]
     */
    protected function transformColumns(array $columns): array
    {
        $results   = [];
        $previous  = null;
        $first     = reset($columns);
        $firstName = $first['name'];
        foreach ($columns as $column) {
            $name           = $column['name'];
            $results[$name] = $this->processColumn($column);
            if ($name === $firstName) {
                $results[$name]['isFirst'] = true;
            }

            $results[$name]['afterField'] = $previous;
            $previous                     = $name;
        }

        return $results;
    }
}
