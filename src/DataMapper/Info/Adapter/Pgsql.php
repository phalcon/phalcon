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

use function explode;
use function is_string;
use function trim;

/**
 * @phpstan-import-type ColumnDefinitionSql from AdapterInterface
 * @phpstan-import-type ColumnDefinition from AdapterInterface
 */
class Pgsql extends AbstractAdapter
{
    protected string $currentSchemaSql = 'SELECT CURRENT_SCHEMA';

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
        $statement = "
            SELECT
                c.column_name,
                c.column_default
            FROM information_schema.columns c
                LEFT JOIN information_schema.key_column_usage kcu
                    ON  c.table_schema = kcu.table_schema
                    AND c.table_name   = kcu.table_name
                    AND c.column_name  = kcu.column_name
                LEFT JOIN information_schema.table_constraints tc
                    ON  kcu.table_schema    = tc.table_schema
                    AND kcu.table_name      = tc.table_name
                    AND kcu.constraint_name = tc.constraint_name
            WHERE
                c.table_schema = :schema
                AND tc.constraint_type = 'PRIMARY KEY'
                AND c.table_name = :table
                AND c.column_default LIKE 'nextval(%'
            ORDER BY c.ordinal_position
        ";

        $defaultValue = $this->connection->fetchValue(
            $statement,
            [
                'schema' => $schema,
                'table'  => $table,
            ]
        );

        if (null !== $defaultValue && false !== $defaultValue) {
            return $defaultValue;
        }

        return null;
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
     * Returns the SQL for the auto increment column
     *
     * @return string
     */
    protected function getAutoIncSql(): string
    {
        return "CASE SUBSTRING(c.column_default FROM 1 FOR 7) "
            . "WHEN 'nextval' "
            . "THEN 1 "
            . "ELSE 0 "
            . "END";
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
        $statement = "
            SELECT 
                i.column_name AS name, 
                d.description AS comment
            FROM pg_catalog.pg_statio_all_tables AS s
            JOIN pg_catalog.pg_description d 
                ON d.objoid = s.relid
            JOIN information_schema.columns i 
                ON d.objsubid = i.ordinal_position 
                AND i.table_schema = s.schemaname 
                AND i.table_name = s.relname
            WHERE i.table_schema = :schema
            AND i.table_name = :table
        ";
        /** @var array<string, string> $comments */
        $comments = $this->connection->fetchPairs(
            $statement,
            [
                'schema' => $schema,
                'table'  => $table,
            ]
        );

        foreach ($comments as $name => $comment) {
            if (isset($columns[$name])) {
                $columns[$name]['comment'] = $comment;
            }
        }

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
        /**
         * If this is a string literal
         */
        if (is_string($defaultValue)) {
            $parts = explode('::', $defaultValue);
            if (2 === count($parts)) {
                $defaultValue = trim($parts[0], "'");
            }
        }

        return parent::processDefault($defaultValue, $type);
    }
}
