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

use function array_column;
use function array_map;
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
                c.column_name AS name,
                c.data_type AS type,
                COALESCE(
                    c.character_maximum_length,
                    c.numeric_precision
                ) AS size,
                c.numeric_scale AS numeric_scale,
                CASE
                    WHEN c.is_nullable = 'YES' THEN 1
                    ELSE 0
                END AS is_nullable,
                c.column_default AS default_value,
                $autoInc AS is_auto_increment,
                CASE
                    WHEN tc.constraint_type = 'PRIMARY KEY' THEN 1
                    ELSE 0
                END AS is_primary,
                $extended AS extended
            FROM information_schema.columns c
                LEFT JOIN information_schema.key_column_usage kcu
                    ON  c.table_schema = kcu.table_schema
                    AND c.table_name = kcu.table_name
                    AND c.column_name = kcu.column_name
                LEFT JOIN information_schema.table_constraints tc
                    ON  kcu.table_schema = tc.table_schema
                    AND kcu.table_name = tc.table_name
                    AND kcu.constraint_name = tc.constraint_name
            WHERE c.table_schema = :schema
            AND   c.table_name = :table
            ORDER BY c.ordinal_position
        ";

        /** @var ColumnDefinitionSql[] $columns */
        $columns = $this->connection->fetchAll(
            $statement,
            [
                'schema' => $schema,
                'table'  => $table,
            ]
        );

        return array_column(
            array_map(
                [$this, 'processColumn'],
                $columns
            ),
            null,
            'name'
        );
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
            'name'            => $column['name'],
            'type'            => $column['type'],
            'size'            => isset($column['size'])
                ? (int)$column['size']
                : null,
            'scale'           => isset($column['numeric_scale'])
                ? (int)$column['numeric_scale']
                : null,
            'isNullable'      => (bool)$column['is_nullable'],
            'defaultValue'    => $this->processDefault(
                $column['default_value'],
                $column['type']
            ),
            'isAutoIncrement' => (bool)$column['is_auto_increment'],
            'isPrimary'       => (bool)$column['is_primary'],
            'isUnsigned'      => $this->processSigned(
                $column['extended'],
                $column['type']
            ),
            'options'         => null,
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
        $floatTypes   = ['decimal', 'double', 'float', 'numeric', 'real'];
        $keywordTypes = ['CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP'];

        if (
            null === $defaultValue ||
            true === in_array(strtoupper((string)$defaultValue), $keywordTypes)
        ) {
            return null;
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
    protected function processSigned(string $value, string $type): bool | null
    {
        return null;
    }
}
