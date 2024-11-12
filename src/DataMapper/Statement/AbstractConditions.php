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

use function is_numeric;

abstract class AbstractConditions extends AbstractStatement
{
    /**
     * Sets a `AND` for a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function andWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->where($condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `WHERE` clause
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function appendWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->appendCondition('WHERE', $condition, $value, $type);

        return $this;
    }

    /**
     * Sets the `LIMIT` clause
     *
     * @param int $limit
     *
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->store['LIMIT'] = $limit;

        if ($this->store['PAGE'] > 0) {
            $this->store['PAGE']   = 0;
            $this->store['OFFSET'] = 0;
        }

        return $this;
    }

    /**
     * Sets the `OFFSET` clause
     *
     * @param int $offset
     *
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->store['OFFSET'] = $offset;

        if ($this->store['PAGE'] > 0) {
            $this->store['PAGE']  = 0;
            $this->store['LIMIT'] = 0;
        }

        return $this;
    }

    /**
     * Sets a `OR` for a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function orWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition('WHERE', 'OR ', $condition, $value, $type);

        return $this;
    }

    /**
     * Sets the `ORDER BY`
     *
     * @param array|string $orderBy
     *
     * @return static
     */
    public function orderBy(mixed $orderBy): static
    {
        $this->processValue('ORDER', $orderBy);

        return $this;
    }

    /**
     * Sets the `PAGE`
     *
     * @param int $page
     *
     * @return static
     */
    public function page(int $page): static
    {
        $this->store['PAGE'] = $page;
        $this->setPagingLimitOffset();

        return $this;
    }

    /**
     * Sets the `PER_PAGE`
     *
     * @param int $perPage
     *
     * @return $this
     */
    public function perPage(int $perPage): static
    {
        $this->store['PER_PAGE'] = $perPage;

        if ($this->store['PAGE']) {
            $this->setPagingLimitOffset();
        }

        return $this;
    }

    /**
     * Resets the limit and offset
     *
     * @return $this
     */
    public function resetLimit(): static
    {
        $this->store['LIMIT']    = 0;
        $this->store['OFFSET']   = 0;
        $this->store['PAGE']     = 0;
        $this->store['PER_PAGE'] = 10;

        return $this;
    }

    /**
     * Resets the order by
     *
     * @return $this
     */
    public function resetOrderBy(): static
    {
        $this->store['ORDER'] = [];

        return $this;
    }

    /**
     * Resets the where
     *
     * @return $this
     */
    public function resetWhere(): static
    {
        $this->store['WHERE'] = [];

        return $this;
    }

    /**
     * Sets a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function where(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition('WHERE', 'AND ', $condition, $value, $type);

        return $this;
    }

    /**
     * @param array $columnsValues
     *
     * @return static
     */
    public function whereEquals(array $columnsValues): static
    {
        foreach ($columnsValues as $key => $value) {
            $arguments = match (true) {
                is_numeric($key) => [$value],
                null === $value  => [$key . ' IS NULL'],
                [] === $value    => ['FALSE'],
                is_array($value) => [$key . ' IN ', $value],
                default          => [$key . ' = ', $value],
            };

            $this->where(...$arguments);
        }

        return $this;
    }

    /**
     * Appends a conditional
     *
     * @param string     $store
     * @param string     $andor
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     */
    protected function addCondition(
        string $store,
        string $andor,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): void {
        if (!empty($value)) {
            $condition .= $this->bindInline($value, $type);
        }

        if (empty($this->store[$store])) {
            $andor = '';
        }

        $this->store[$store][] = $andor . $condition;
    }

    /**
     * Concatenates a conditional
     *
     * @param string $store
     * @param string $condition
     * @param mixed  $value
     * @param int    $type
     */
    protected function appendCondition(
        string $store,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): void {
        if (!empty($value)) {
            $condition .= $this->bindInline($value, $type);
        }

        if (empty($this->store[$store])) {
            $this->store[$store][] = '';
        }

        $key = array_key_last($this->store[$store]);

        $this->store[$store][$key] .= $condition;
    }

    /**
     * Builds the `LIMIT` clause
     *
     * @return string
     */
    protected function buildLimit(): string
    {
        $limit = '';

        if ('sqlsrv' !== $this->driver) {
            if (0 !== $this->store['LIMIT']) {
                $limit .= 'LIMIT ' . $this->store['LIMIT'];
            }

            if (0 !== $this->store['OFFSET']) {
                $limit .= ' OFFSET ' . $this->store['OFFSET'];
            }

            if ('' !== $limit) {
                $limit = ' ' . ltrim($limit);
            }
        } else {
            if ($this->store['LIMIT'] > 0 && $this->store['OFFSET'] > 0) {
                $limit = ' OFFSET ' . $this->store['OFFSET'] . ' ROWS'
                    . ' FETCH NEXT ' . $this->store['LIMIT'] . ' ROWS ONLY';
            }
        }

        return $limit;
    }

    /**
     * Builds the early `LIMIT` clause - MS SQLServer
     *
     * @return string
     */
    protected function buildLimitEarly(): string
    {
        $limit = '';

        if (
            'sqlsrv' === $this->driver &&
            $this->store['LIMIT'] > 0 &&
            0 === $this->store['OFFSET']
        ) {
            $limit = ' TOP ' . $this->store['LIMIT'];
        }

        return $limit;
    }

    /**
     * Calculates the pages/limit/offset
     *
     * @return void
     */
    protected function setPagingLimitOffset(): void
    {
        $this->store['LIMIT']  = 0;
        $this->store['OFFSET'] = 0;

        if ($this->store['PAGE'] > 0) {
            $this->store['LIMIT']  = $this->store['PER_PAGE'];
            $this->store['OFFSET'] = $this->store['PER_PAGE'] * ($this->store['PAGE'] - 1);
        }
    }
}
