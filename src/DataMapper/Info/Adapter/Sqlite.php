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

use function preg_match;
use function preg_match_all;
use function preg_replace;
use function str_contains;
use function str_replace;
use function strcasecmp;
use function strpos;
use function strtolower;
use function substr;
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
     * @param string $schema
     * @param string $table
     *
     * @return string
     */
    protected function getListColumnSql(string $schema, string $table): string
    {
        $currentSchema = $this->quote($schema);
        $currentTable  = $this->quote($table);

        return "PRAGMA $currentSchema.table_info($currentTable)";
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
        $sizePattern = "#\\((\d+)(?:,\\s*(\d+))*\\)#";
        $type        = strtolower(trim($column['type'])); // char(10)

        /**
         * If the column type has a parentheses we try to get the column
         * size from it
         */
        $matches = [];
        $size    = null;
        $scale   = null;
        if (
            str_contains($type, "(") &&
            preg_match($sizePattern, $type, $matches)
        ) {
            $size  = (int)$matches[1];
            $scale = isset($matches[2]) ? (int)$matches[2] : null;
        }

        /**
         * Get the type by removing any parentheses
         */
        $position = strpos($type, '(');
        if (false !== $position) {
            $type = substr($type, 0, $position);
        }
        /**
         * Check if the column is numeric
         */
        $isNumeric = match (true) {
            str_contains($type, 'int'),
            str_contains($type, 'double'),
            str_contains($type, 'float'),
            str_contains($type, 'numeric'),
            str_contains($type, 'real') => true,
            default                     => false
        };

        /**
         * Check if the column is default values
         * When field is empty default value is null
         */
        $defaultValue = $column['dflt_value'];
        if (
            true !== empty($defaultValue) &&
            0 !== strcasecmp($defaultValue, "null")
        ) {
            $defaultValue = preg_replace(
                "/^'|'$/",
                "",
                $defaultValue
            );
        }
        [$defaultValue, $hasDefault] = $this->processDefault(
            $defaultValue,
            $type
        );

        return [
            'afterField'      => null,
            'comment'         => '',
            'default'         => $defaultValue,
            'hasDefault'      => $hasDefault,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => 0 !== $column['notnull'],
            'isNumeric'       => $isNumeric,
            'isPrimary'       => (bool)($column['pk']),
            'isUnsigned'      => false, // Sqlite does not have unsigned
            'name'            => $column['name'],
            'options'         => null,
            'scale'           => $scale,
            'size'            => $size,
            'type'            => $type,
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
         * Get the CREATE SQL from SQLite to figure out the autoincrement
         */
        $createSql = $this->getTableSql($schema, $table);
        $pattern   = '/^\s*(\w+)\s+.*?AUTOINCREMENT/im';
        preg_match_all($pattern, $createSql, $matches, PREG_SET_ORDER);

        /**
         * Loop through the matches and update the $columns array
         */
        foreach ($matches as $match) {
            $columns[$match[1]]['isAutoIncrement'] = true;
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
