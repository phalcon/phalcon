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
 * @link    https://github.com/atlasphp/Atlas.Statement
 * @license https://github.com/atlasphp/Atlas.Statement/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function is_int;
use function ltrim;

class Insert extends AbstractStatement
{
    /**
     * Insert constructor.
     *
     * @param string $driver
     */
    public function __construct(string $driver)
    {
        parent::__construct($driver);

        $this->store['FROM']      = '';
        $this->store['RETURNING'] = [];
    }

    /**
     * Sets a column for the `INSERT` query
     *
     * @param string $column
     *
     * @return Insert
     */
    public function column(
        string $column,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->store['COLUMNS'][$column] = ':' . $column;

        if (null !== $value) {
            $this->bind->setValue($column, $value, $type);
        }

        return $this;
    }

    /**
     * Mass sets columns and values for the `INSERT`
     *
     * @param array $columns
     *
     * @return Insert
     */
    public function columns(array $columns): static
    {
        foreach ($columns as $column => $value) {
            if (is_int($column)) {
                $this->column($value);
            } else {
                $this->column($column, $value);
            }
        }

        return $this;
    }

    /**
     * Returns the generated statement
     *
     * @return string
     */
    public function getStatement(): string
    {
        return 'INSERT'
            . $this->buildFlags()
            . ' INTO ' . $this->store['FROM']
            . $this->buildColumns()
            . $this->buildReturning();
    }

    /**
     * Return the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->store['FROM'];
    }

    /**
     * Adds table(s) in the query
     *
     * @param string $table
     *
     * @return Insert
     */
    public function into(string $table): static
    {
        $this->store['FROM'] = $table;

        return $this;
    }

    /**
     * Resets the internal store
     *
     * @return void
     */
    public function reset(): void
    {
        parent::reset();

        $this->store['FROM']      = '';
        $this->store['RETURNING'] = [];
    }

    /**
     * Resets the `RETURNING` store
     *
     * @return $this
     */
    public function resetReturning(): static
    {
        $this->store['RETURNING'] = [];

        return $this;
    }

    /**
     * Adds the `RETURNING` clause
     *
     * @param array $columns
     *
     * @return $this
     */
    public function returning(array $columns): static
    {
        $this->store['RETURNING'] = array_merge(
            $this->store['RETURNING'],
            $columns
        );

        return $this;
    }

    /**
     * Sets a column = value condition
     *
     * @param string     $column
     * @param mixed|null $value
     *
     * @return Insert
     */
    public function set(string $column, mixed $value = null): static
    {
        if (null === $value) {
            $value = 'NULL';
        }

        $this->store['COLUMNS'][$column] = $value;

        $this->bind->remove($column);

        return $this;
    }

    /**
     * Builds the column list
     *
     * @return string
     */
    private function buildColumns(): string
    {
        /**
         * Using `array_map` instead of a foreach. It might not be easy to
         * understand at first glance but it is the fastest implementation.
         *
         * We are traversing the array and quoting every column.        $driver = env('driver');
         * $insert = Insert::new($driver);
         */
        $columns = array_map(
            fn($column) => $this->quoteIdentifier($column),
            array_keys($this->store['COLUMNS'])
        );

        return ' ('
            . ltrim($this->indent($columns, ','))
            . ') VALUES ('
            . ltrim($this->indent(array_values($this->store['COLUMNS']), ','))
            . ')';
    }
}
