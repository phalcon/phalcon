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
use function is_int;

class Update extends Delete
{
    /**
     * Sets a column for the `UPDATE` query
     *
     * @param string     $column
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
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
     * Mass sets columns and values for the `UPDATE`
     *
     * @param array<array-key, mixed> $columns
     *
     * @return static
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
     * @return string
     */
    public function getStatement(): string
    {
        return $this->buildWith()
            . 'UPDATE'
            . $this->buildFlags()
            . ' ' . $this->store['FROM']
            . $this->buildColumns()
            . $this->buildCondition('WHERE')
            . $this->buildLimit()
            . $this->buildReturning();
    }

    /**
     * Whether the query has columns or not
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return count($this->store['COLUMNS']) > 0;
    }

    /**
     * Sets a column = value condition
     *
     * @param string     $column
     * @param mixed|null $value
     *
     * @return static
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
        $assignments = array_map(
            fn($column, $value) => $this->quoteIdentifier($column) . ' = ' . $value,
            array_keys($this->store['COLUMNS']),
            $this->store['COLUMNS']
        );

        return ' SET' . $this->indent($assignments, ',');
    }
}
