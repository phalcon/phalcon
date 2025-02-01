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

use function str_contains;
use function trim;

/**
 * @phpstan-import-type ColumnDefinitionSql from AdapterInterface
 * @phpstan-import-type ColumnDefinition from AdapterInterface
 */
class Mysql extends AbstractAdapter
{
    protected string $currentSchemaSql = 'SELECT DATABASE()';

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
            WHEN LOCATE('auto_increment', c.EXTRA) > 0 THEN 1
            ELSE 0
        END ";
    }

    /**
     * Returns the SQL for the extended field (MySQL)
     *
     * @return string
     */
    protected function getExtendedSql(): string
    {
        return 'c.column_type';
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
