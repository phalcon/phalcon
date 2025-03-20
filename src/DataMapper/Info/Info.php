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

namespace Phalcon\DataMapper\Info;

use Phalcon\DataMapper\Info\Adapter\AdapterInterface;
use Phalcon\DataMapper\Info\Adapter\Mysql;
use Phalcon\DataMapper\Info\Adapter\Pgsql;
use Phalcon\DataMapper\Info\Adapter\Sqlite;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Exception\Exception;

/**
 * @phpstan-import-type ColumnDefinition from AdapterInterface
 */
class Info
{
    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(
        protected AdapterInterface $adapter
    ) {
    }

    /**
     * Returns the autoincrement or sequence
     *
     * @param string $schemaTable
     *
     * @return string|null
     */
    public function getAutoincSequence(string $schemaTable): string | null
    {
        [$schema, $table] = $this->adapter->listSchemaTable($schemaTable);

        return $this->adapter->getAutoincSequence($schema, $table);
    }

    /**
     * Return the current schema name
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentSchema(): string
    {
        return $this->adapter->getCurrentSchema();
    }

    /**
     * Return the columns in an array with their respective properties
     *
     * @param string $schemaTable
     *
     * @return ColumnDefinition[]
     * @throws Exception
     */
    public function listColumns(string $schemaTable): array
    {
        [$schema, $table] = $this->adapter->listSchemaTable($schemaTable);

        return $this->adapter->listColumns($schema, $table);
    }

    /**
     * Returns an array with the available tables for the schema
     *
     * @param string|null $schema
     *
     * @return array<array-key, string>
     * @throws Exception
     */
    public function listTables(string | null $schema = null): array
    {
        return $this->adapter->listTables(
            $schema ?? $this->getCurrentSchema()
        );
    }

    /**
     * @param Connection $connection
     *
     * @return Info
     */
    public static function new(Connection $connection): Info
    {
        $adapters = self::getAdapters();
        $type     = $connection->getDriverName();
        $adapter  = $adapters[$type] ?? null;
        if (null === $adapter) {
            throw new Exception('Adapter [' . $type . '] not supported');
        }

        return new static(new $adapter($connection));
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected static function getAdapters(): array
    {
        return [
            'mysql'  => Mysql::class,
            'pgsql'  => Pgsql::class,
            'sqlite' => Sqlite::class,
        ];
    }
}
