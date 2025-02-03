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
    public function getAutoincSequence(string $schema, string $table): ?string
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
        return "CASE
            WHEN SUBSTRING(c.COLUMN_DEFAULT FROM 1 FOR 7) = 'nextval' THEN 1
            ELSE 0
        END";
    }

    /**
     * Returns the actual default value of a column
     *
     * @param mixed $defaultValue
     * @param string $type
     *
     * @return mixed
     */
    protected function getDefault(
        mixed $defaultValue,
        string $type
    ): mixed {
        /**
         * Check if the value is null
         */
        if (null === $defaultValue || 'NULL' === strtoupper($defaultValue)) {
            return null;
        }

        /**
         * Check if this is a numeric value
         */
        if (is_numeric($defaultValue)) {
            return $defaultValue;
        }

        /**
         * If this is a string literal
         */
        $parts = explode('::', $defaultValue);
        if (2 === count($parts)) {
            return trim($parts[0], "'");
        }

        return null;
    }
}
