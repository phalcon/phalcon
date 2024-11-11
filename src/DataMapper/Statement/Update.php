<?php

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
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
     * @return $this
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
     * @param array $columns
     *
     * @return $this
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
     * @return $this
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
