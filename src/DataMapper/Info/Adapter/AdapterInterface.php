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
 *     _name: string,
 *     _type: string,
 *     _size?: int,
 *     _scale?: int,
 *     _notnull: bool,
 *     _default: mixed,
 *     _autoinc: bool,
 *     _primary: bool,
 *     _options: mixed
 * }
 *
 * @phpstan-type ColumnDefinition = array{
 *     name: string,
 *     type: string,
 *     size: int|null,
 *     scale: int|null,
 *     notnull: bool,
 *     default: mixed,
 *     autoinc: bool,
 *     primary: bool,
 *     options: mixed
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
    public function getAutoincSequence(string $schema, string $table): string|null;

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
