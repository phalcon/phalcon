<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Db;

use Phalcon\Db\Exceptions\InvalidIndexColumns;
use Phalcon\Db\Exceptions\InvalidIndexDirections;
use Phalcon\Db\Exceptions\InvalidIndexWhere;

/**
 * Allows to define indexes to be used on tables. Indexes are a common way
 * to enhance database performance. An index allows the database server to find
 * and retrieve specific rows much faster than it could do without an index.
 *
 * The constructor accepts either the legacy positional form (a plain array
 * of column names) or a definition-array form (an associative array with a
 * `columns` key); the latter is the path used by features such as
 * `invisible` (MySQL 8.0+), `directions`, `where`, and `concurrently`.
 *
 *```php
 * // Legacy positional form
 * $unique = new \Phalcon\Db\Index(
 *     'column_UNIQUE',
 *     [
 *         'column',
 *     ],
 *     'UNIQUE'
 * );
 *
 * $primary = new \Phalcon\Db\Index(
 *     'PRIMARY',
 *     [
 *         'column',
 *     ]
 * );
 *
 * // Definition-array form (MySQL 8.0+ invisible index)
 * $hidden = new \Phalcon\Db\Index(
 *     'idx_hidden',
 *     [
 *         'columns'    => ['col1'],
 *         'type'       => '',
 *         'invisible'  => true,
 *         'directions' => ['DESC'],
 *     ]
 * );
 *```
 */
class Index implements IndexInterface
{
    /**
     * Index columns. Entries may be plain strings (column names) or
     * `Phalcon\Db\RawValue` instances (functional/expression index entries).
     */
    protected array $columns;

    /**
     * Whether to build the index without taking a strong lock that blocks
     * writes - emits `CONCURRENTLY` between `INDEX` and the index name on
     * PostgreSQL. MySQL and SQLite ignore the flag.
     */
    protected bool $concurrent = false;

    /**
     * Per-column sort directions (`ASC` / `DESC`). Empty array means
     * "emit no per-column direction" - preserves the legacy plain
     * `(col1, col2)` rendering.
     */
    protected array $directions = [];

    /**
     * Whether the index is declared `INVISIBLE` (MySQL 8.0+).
     */
    protected bool $invisible = false;

    /**
     * Optional partial-index `WHERE` predicate. Supported by PostgreSQL and
     * SQLite. Empty string means no predicate.
     */
    protected string $where = "";

    /**
     * Phalcon\Db\Index constructor.
     *
     * Accepts either the legacy positional form `(name, columns, type)` or
     * a definition-array form `(name, ["columns" => [...], "type" => "...",
     * "invisible" => true, ...])`. Detection is based on the presence of a
     * `columns` key in the second argument; when present, the third
     * positional `type` argument is ignored in favor of the definition.
     *
     * @throws Exception
     */
    public function __construct(
        protected string $name,
        array $columnsOrDefinition,
        protected string $type = ""
    ) {
        if (isset($columnsOrDefinition['columns'])) {
            if (!is_array($columnsOrDefinition['columns'])) {
                throw new InvalidIndexColumns();
            }

            $this->columns = $columnsOrDefinition['columns'];

            if (isset($columnsOrDefinition['type'])) {
                $this->type = (string) $columnsOrDefinition['type'];
            }

            if (isset($columnsOrDefinition['invisible'])) {
                $this->invisible = (bool) $columnsOrDefinition['invisible'];
            }

            if (isset($columnsOrDefinition['directions'])) {
                if (!is_array($columnsOrDefinition['directions'])) {
                    throw new InvalidIndexDirections();
                }
                $this->directions = $columnsOrDefinition['directions'];
            }

            if (isset($columnsOrDefinition['where'])) {
                if (!is_string($columnsOrDefinition['where'])) {
                    throw new InvalidIndexWhere();
                }
                $this->where = $columnsOrDefinition['where'];
            }

            if (isset($columnsOrDefinition['concurrently'])) {
                $this->concurrent = (bool) $columnsOrDefinition['concurrently'];
            }
        } else {
            $this->columns = $columnsOrDefinition;
        }
    }

    /**
     * Index columns
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the per-column sort directions array (`ASC` / `DESC`).
     * Empty array means the index was declared without explicit per-column
     * directions.
     */
    public function getDirections(): array
    {
        return $this->directions;
    }

    /**
     * Index name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Index type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the partial-index `WHERE` predicate, or an empty string when
     * the index has none.
     */
    public function getWhere(): string
    {
        return $this->where;
    }

    /**
     * Whether the index is built `CONCURRENTLY` (PostgreSQL only).
     */
    public function isConcurrent(): bool
    {
        return $this->concurrent;
    }

    /**
     * Whether the index is declared `INVISIBLE` (MySQL 8.0+).
     */
    public function isInvisible(): bool
    {
        return $this->invisible;
    }
}
