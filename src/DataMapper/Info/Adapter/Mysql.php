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

use Phalcon\DataMapper\Pdo\Exception\Exception;

use function array_map;
use function explode;
use function str_contains;
use function str_getcsv;
use function stripos;
use function substr;
use function trim;

/**
 * @phpstan-type ColumnDefinitionSql = array{
 *     name: string,
 *     type: string,
 *     size?: int,
 *     scale?: int,
 *     isNullable: bool,
 *     defaultValue: mixed,
 *     isAutoIncrement: bool,
 *     isPrimary: bool,
 *     options: mixed,
 *     extended: string
 * }
 *
 * @phpstan-type ColumnDefinition = array{
 *      name: string,
 *      type: string,
 *      size: int|null,
 *      scale: int|null,
 *      isNullable: bool,
 *      defaultValue: mixed,
 *      isAutoIncrement: bool,
 *      isPrimary: bool,
 *      isUnsigned: ?bool,
 *      options: mixed
 * }
 */
class Mysql extends AbstractAdapter
{
    /**
     * Return the current schema name
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentSchema(): string
    {
        /** @var string $currentSchema */
        $currentSchema = $this->connection->fetchValue('SELECT DATABASE()');

        return $currentSchema;
    }

    /**
     * Return the columns in an array with their respective properties
     *
     * @param string $schema
     * @param string $table
     *
     * @return ColumnDefinition[]
     * @throws Exception
     */
    public function listColumns(string $schema, string $table): array
    {
        $statement = "
            SELECT
                c.column_name AS name,
                c.data_type AS type,
                COALESCE(
                    c.character_maximum_length,
                    c.numeric_precision
                ) AS size,
                c.numeric_scale AS scale,
                CASE
                    WHEN c.is_nullable = 'YES' THEN 1
                    ELSE 0
                END AS isNullable,
                c.column_default AS defaultValue,
                CASE
                    WHEN LOCATE('auto_increment', c.EXTRA) > 0 THEN 1
                    ELSE 0
                END AS isAutoIncrement,
                CASE
                    WHEN tc.constraint_type = 'PRIMARY KEY' THEN 1
                    ELSE 0
                END AS isPrimary,
                c.column_type as extended
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
     * @param string $input
     *
     * @return string[]
     * @throws Exception
     */
    public function listSchemaTable(string $input): array
    {
        $parts = explode('.', $input, 2);

        if (count($parts) === 1) {
            return [
                $this->getCurrentSchema(),
                $input,
            ];
        }

        return $parts;
    }

    /**
     * Returns an array with the available tables for the schema
     *
     * @param string $schema
     *
     * @return array<array-key, string>
     * @throws Exception
     */
    public function listTables(string $schema): array
    {
        $statement = '
            SELECT table_name
            FROM   information_schema.tables
            WHERE  table_schema = :schema
            AND    UPPER(table_type) = :type
            ORDER BY table_name
        ';

        return $this->connection->fetchColumn(
            $statement,
            [
                'schema' => $schema,
                'type'   => 'BASE TABLE',
            ]
        );
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
            'size'            => isset($column['size']) ? (int)$column['size'] : null,
            'scale'           => isset($column['scale']) ? (int)$column['scale'] : null,
            'isNullable'      => (bool)$column['isNullable'],
            'defaultValue'    => $this->processDefault(
                $column['defaultValue'],
                $column['type']
            ),
            'isAutoIncrement' => (bool)$column['isAutoIncrement'],
            'isPrimary'       => (bool)$column['isPrimary'],
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
     * @param string $value
     * @param string $type
     *
     * @return bool|null
     */
    protected function processSigned(string $value, string $type): bool | null
    {
        $extended = trim($value);

        /**
         * Unsigned
         */
        if (str_contains($extended, 'unsigned') && str_contains($type, 'int')) {
            return true;
        }

        return str_contains($type, 'int') ? false : null;
    }
}
