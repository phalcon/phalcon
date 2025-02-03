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

use function array_column;
use function array_map;
use function preg_match;
use function preg_match_all;
use function str_contains;
use function str_replace;
use function stripos;
use function strtolower;
use function trim;

use const PREG_SET_ORDER;

/**
 * @phpstan-import-type ColumnDefinitionSql from AdapterInterface
 * @phpstan-import-type ColumnDefinition from AdapterInterface
 */
class Sqlite extends AbstractAdapter
{
    /**
     * Return the current schema name
     *
     * @return string
     */
    public function getCurrentSchema(): string
    {
        return 'main';
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
        $currentSchema = $this->quote($schema);
        $currentTable  = $this->quote($table);
        $statement     = "PRAGMA $currentSchema.table_info($currentTable)";

        $columns = $this->connection->fetchAll($statement);

        /**
         * Format the columns
         */
        $processed = $this->transformColumns($columns);

        /**
         * Add autoinc and defaults and return
         */
        return $this->processColumnInformation($schema, $table, $processed);
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
        $schema = $this->quote($schema);

        $statement = "
            SELECT name
            FROM $schema.sqlite_master
            WHERE type = :type
        ";

        return $this->connection->fetchColumn(
            $statement,
            [
                'type' => 'table',
            ]
        );
    }

    /**
     * Returns the SQL statement that creates the table. Useful in parsing
     * the default values and autoincrement columns
     *
     * @param string $schema
     * @param string $table
     *
     * @return string
     * @throws Exception
     */
    protected function getTableSql(string $schema, string $table): string
    {
        $schema    = $this->quote($schema);
        $statement = "
            SELECT sql
            FROM $schema.sqlite_master
            WHERE type = :type AND name = :table
        ";

        return $this->connection->fetchValue(
            $statement,
            [
                'type'  => 'table',
                'table' => $table,
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
        /**
         * Regular expression to match the field type, size, and scale
         */
        $pattern = '/^([^(]+)(?:\((\d+)(?:\s*,\s*(\d+))?\))?$/';
        $type    = strtolower(trim($column['type']));
        preg_match($pattern, $type, $matches);

        /**
         * Check if the column is numeric
         */
        $isNumeric = match (true) {
            str_contains($type, 'int'),
            str_contains($type, 'double'),
            str_contains($type, 'float'),
            str_contains($type, 'numeric'),
            str_contains($type, 'real') => true,
            default => false
        };

        /**
         * Extract the type, size, and scale from the matches
         */
        return [
            'afterField'      => null,
            'comment'         => '',
            'default'         => $this->processDefault(
                $column['dflt_value'],
                $column['type']
            ),
            'hasDefault'      => null !== $column['dflt_value'],
            'isAutoIncrement' => null,
            'isFirst'         => null,
            'isNotNull'       => !((bool)($column['notnull'])),
            'isNumeric'       => $isNumeric,
            'isPrimary'       => (bool)($column['pk']),
            'isUnsigned'      => null,
            'name'            => $column['name'],
            'options'         => null,
            'scale'           => isset($matches[3]) ? (int)$matches[3] : null,
            'size'            => isset($matches[2]) ? (int)$matches[2] : null,
            'type'            => strtolower($matches[1]),
        ];
    }

    /**
     * @param string                          $schema
     * @param string                          $table
     * @param array<string, ColumnDefinition> $columns
     *
     * @return array<string, ColumnDefinition>
     * @throws Exception
     */
    protected function processColumnInformation(
        string $schema,
        string $table,
        array $columns
    ): array {
        /**
         * Get the CREATE SQL from SQLite to figure out the defaults and
         * autoincrement
         */
        $createSql = $this->getTableSql($schema, $table);
        $pattern   = '/^\s*(\w+)\s+.*?(DEFAULT\s+([^\s,]+)|AUTOINCREMENT)/im';

        /**
         * Find auto increment column as well as the default values
         */
        preg_match_all($pattern, $createSql, $matches, PREG_SET_ORDER);

        /**
         * Loop through the matches and update the $columns array
         */
        foreach ($matches as $match) {
            $fieldName = $match[1];
            if (false !== stripos($match[0], 'AUTOINCREMENT')) {
                $columns[$fieldName]['isAutoIncrement'] = true;
            }
            if (isset($match[3])) {
                $columns[$fieldName]['defaultValue'] = $this->processDefault(
                    trim($match[3], "'"),
                    $columns[$fieldName]['type']
                );
            }
        }

        return $columns;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function quote(string $name): string
    {
        return '"' . str_replace('"', '""', $name) . '"';
    }
}
