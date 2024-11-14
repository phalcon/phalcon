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

use function array_key_last;
use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function implode;
use function is_int;
use function ltrim;
use function strtoupper;
use function trim;

class Select extends AbstractConditions
{
    public const JOIN_INNER   = "INNER";
    public const JOIN_LEFT    = "LEFT";
    public const JOIN_NATURAL = "NATURAL";
    public const JOIN_RIGHT   = "RIGHT";

    /**
     * @var string
     */
    protected string $asAlias = '';

    /**
     * @var bool
     */
    protected bool $forUpdate = false;

    /**
     * Sets a `AND` for a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function andHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->having($condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `HAVING` clause
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function appendHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->appendCondition('HAVING', $condition, $value, $type);

        return $this;
    }

    /**
     * /**
     * Concatenates to the most recent `JOIN` clause
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function appendJoin(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        if (!empty($value)) {
            $condition .= $this->bind->inline($value, $type);
        }

        $end = array_key_last($this->store['FROM']);
        $key = array_key_last($this->store['FROM'][$end]);

        $this->store['FROM'][$end][$key] .= $condition;

        return $this;
    }

    /**
     * The `AS` statement for the query - useful in sub-queries
     *
     * @param string $asAlias
     *
     * @return static
     */
    public function asAlias(string $asAlias): static
    {
        $this->asAlias = $asAlias;

        return $this;
    }

    /**
     * The columns to select from. If a key is set in the array element, the
     * key will be used as the alias
     *
     * @param array $columns
     *
     * @return static
     */
    public function columns(array $columns): static
    {
        /**
         * This code is not the easiest to understand but it is the fastest.
         *
         * Using `array_map` will traverse the array without the need for a
         * foreach and a separate copy. The loop checks if the key of the
         * array is a string or an integer. If it is an integer, it uses the
         * value as is. If it is a string, the key is used as an alias for
         * the field
         */
        $this->store['COLUMNS'] = array_merge(
            $this->store['COLUMNS'],
            array_map(
                fn($key, $value) => is_int($key) ? $value : $value . ' AS ' . $key,
                array_keys($columns),
                $columns
            )
        );

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return static
     */
    public function distinct(bool $enable = true): static
    {
        $this->setFlag('DISTINCT', $enable);

        return $this;
    }

    /**
     * Enable the `FOR UPDATE` for the query
     *
     * @param bool $enable
     *
     * @return static
     */
    public function forUpdate(bool $enable = true): static
    {
        $this->forUpdate = $enable;

        return $this;
    }

    /**
     * Adds table(s) in the query
     *
     * @param string|AbstractStatement $table
     *
     * @return static
     */
    public function from(string | AbstractStatement $table): static
    {
        if ($table instanceof AbstractStatement) {
            $this->bind->merge($table->getBindValues());
            $table = $table->getStatement();
        }

        $this->store['FROM'][] = [$table];

        return $this;
    }

    /**
     * Returns the compiled SQL statement
     *
     * @return string
     */
    public function getStatement(): string
    {
        return implode('', $this->store['UNION']) . $this->getCurrentStatement();
    }

    /**
     * Sets the `GROUP BY`
     *
     * @param array|string $groupBy
     *
     * @return static
     */
    public function groupBy(array | string $groupBy): static
    {
        $this->processValue('GROUP', $groupBy);

        return $this;
    }

    /**
     * Whether the query has columns or not
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return !empty($this->store['COLUMNS']);
    }

    /**
     * Sets a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function having(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition('HAVING', 'AND ', $condition, $value, $type);

        return $this;
    }

    /**
     * Sets a 'JOIN' condition
     *
     * @param string                   $join
     * @param string|AbstractStatement $table
     * @param string                   $condition
     * @param mixed|null               $value
     * @param int                      $type
     *
     * @return $this
     */
    public function join(
        string $join,
        string | AbstractStatement $table,
        string $condition = '',
        mixed $value = null,
        int $type = -1
    ): static {
        if ($table instanceof AbstractStatement) {
            $this->bind->merge($table->getBindValues());
            $table = $table->getStatement();
        }

        $join = strtoupper(trim($join));
        if (!str_ends_with($join, 'JOIN')) {
            $join .= ' JOIN';
        }

        $condition = ltrim($condition);

        if (
            '' !== $condition
            && !str_starts_with(strtoupper($condition), 'ON')
            && !str_starts_with(strtoupper($condition), 'USING')
        ) {
            $condition = 'ON ' . $condition;
        }

        if (!empty($value)) {
            $condition .= $this->bind->inline($value, $type);
        }

        $key = array_key_last($this->store['FROM']);

        $this->store['FROM'][$key][] = $join . ' ' . $table . ' ' . $condition;

        return $this;
    }

    /**
     * Sets a `OR` for a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function orHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition('HAVING', 'OR ', $condition, $value, $type);

        return $this;
    }

    /**
     * Resets the internal collections
     */
    public function reset(): void
    {
        parent::reset();

        $this->asAlias   = '';
        $this->forUpdate = false;
    }

    /**
     * Reset the AS clause
     *
     * @return $this
     */
    public function resetAs(): static
    {
        $this->asAlias = '';

        return $this;
    }

    /**
     * Resets the columns
     *
     * @return $this
     */
    public function resetColumns(): static
    {
        $this->store['COLUMNS'] = [];

        return $this;
    }

    /**
     * Resets the flags
     *
     * @return $this
     */
    public function resetFlags(): static
    {
        $this->store['FLAGS'] = [];

        return $this;
    }

    /**
     * Reset the FROM clause
     *
     * @return $this
     */
    public function resetFrom(): static
    {
        $this->store['FROM'] = [];

        return $this;
    }

    /**
     * Resets the group by
     *
     * @return $this
     */
    public function resetGroupBy(): static
    {
        $this->store['GROUP'] = [];

        return $this;
    }

    /**
     * Resets the having
     *
     * @return void
     */
    public function resetHaving(): static
    {
        $this->store['HAVING'] = [];

        return $this;
    }

    /**
     * Start a sub-select
     *
     * @return static
     */
    public function subSelect(): static
    {
        $clone = clone $this;
        $clone->reset();
        $clone->bind->reset();

        return $clone;
    }

    /**
     * Start a `UNION`
     *
     * @return static
     */
    public function union(): static
    {
        $this->store['UNION'][] = $this->getCurrentStatement(' UNION ');

        $this->reset();

        return $this;
    }

    /**
     * Start a `UNION ALL`
     *
     * @return static
     */
    public function unionAll(): static
    {
        $this->store['UNION'][] = $this->getCurrentStatement(' UNION ALL ');

        $this->reset();

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return string
     */
    protected function getCurrentStatement(string $suffix = ''): string
    {
        $forUpdate = '';

        if ($this->forUpdate) {
            $forUpdate = ' FOR UPDATE';
        }

        $statement = $this->buildWith()
            . 'SELECT'
            . $this->buildFlags()
            . $this->buildLimitEarly()
            . $this->buildColumns()
            . $this->buildFrom()
            . $this->buildCondition('WHERE')
            . $this->buildBy('GROUP')
            . $this->buildCondition('HAVING')
            . $this->buildBy('ORDER')
            . $this->buildLimit()
            . $forUpdate;

        if ('' !== $this->asAlias) {
            $statement = '(' . $statement . ') AS ' . $this->asAlias;
        }

        return $statement . $suffix;
    }

    /**
     * Builds the columns list
     *
     * @return string
     */
    private function buildColumns(): string
    {
        $columns = $this->hasColumns() ? $this->store['COLUMNS'] : ['*'];

        return $this->indent($columns, ',');
    }

    /**
     * Builds the from list
     *
     * @return string
     */
    private function buildFrom(): string
    {
        /**
         * Again using `array_map` because it is faster. The 'loop' is used to
         * to get the first element of the array and then shift it off. This
         * is done to avoid using a foreach and a separate copy of the array.
         */
        $from = array_map(
            fn($table) => array_shift($table) . $this->indent($table),
            $this->store['FROM']
        );

        return empty($from) ? '' : ' FROM' . $this->indent($from, ',');
    }
}
