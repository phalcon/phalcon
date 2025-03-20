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

/**
 * @phpstan-type ColumnDefinitionSql = array{
 *      extended: string,
 *      comment: string,
 *      default_value?: mixed,
 *      dflt_value?: mixed,
 *      name: string,
 *      is_auto_increment: bool,
 *      is_first: bool,
 *      is_not_null?: bool,
 *      is_numeric: bool,
 *      is_primary: bool,
 *      is_unsigned: bool|null,
 *      notnull?: int,
 *      numeric_scale: int|null,
 *      pk?: int,
 *      size: int|null,
 *      type: string,
 * }
 *
 * @phpstan-type ColumnDefinition = array{
 *      afterField: string|null,
 *      comment: string,
 *      default: mixed,
 *      hasDefault: bool,
 *      isAutoIncrement: bool,
 *      isFirst: bool,
 *      isNotNull: bool,
 *      isNumeric: bool,
 *      isPrimary: bool,
 *      isUnsigned: bool|null,
 *      name: string,
 *      options: list<string>|null,
 *      scale: int|null,
 *      size: int|null,
 *      type: string
 * }
 */
interface AdapterInterface
{
    /**
     * Returns the autoincrement or sequence
     *
     * @param string $schema
     * @param string $table
     *
     * @return string|null
     */
    public function getAutoincSequence(string $schema, string $table): string | null;

    /**
     * Return the current schema name
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentSchema(): string;

    /**
     * Return the columns in an array with their respective properties
     *
     * @param string $schema
     * @param string $table
     *
     * @return array<string, ColumnDefinition>
     * @throws Exception
     */
    public function listColumns(string $schema, string $table): array;

    /**
     * Return an array with the schema and table name
     *
     * @param string $schemaTable
     *
     * @return string[]
     * @throws Exception
     */
    public function listSchemaTable(string $schemaTable): array;

    /**
     * Returns an array with the available tables for the schema
     *
     * @param string $schema
     *
     * @return array<array-key, string>
     * @throws Exception
     */
    public function listTables(string $schema): array;
}
