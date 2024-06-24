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
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query;

use function array_key_last;
use function is_array;
use function is_numeric;
use function is_string;
use function ltrim;
use function ucfirst;

abstract class AbstractConditions extends AbstractQuery
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
        $this->appendCondition("WHERE", $condition, $value, $type);

        return $this;
    }

    /**
     * Sets the `LIMIT` clause
     *
     * @param int $limit
     *
     * @return AbstractConditions
     */
    public function limit(int $limit): AbstractConditions
    {
        $this->store["LIMIT"] = $limit;

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
        $this->store["OFFSET"] = $offset;

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
        $this->addCondition("WHERE", "OR ", $condition, $value, $type);

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
        $this->processValue("ORDER", $orderBy);

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
        $this->addCondition("WHERE", "AND ", $condition, $value, $type);

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
            if (is_numeric($key)) {
                $this->where($value);
            } elseif (null === $value) {
                $this->where($key . " IS NULL");
            } elseif (is_array($value)) {
                $this->where($key . " IN ", $value);
            } else {
                $this->where($key . " = ", $value);
            }
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
            $andor = "";
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
            $this->store[$store][] = "";
        }

        $key = array_key_last($this->store[$store]);

        $this->store[$store][$key] .= $condition;
    }

    /**
     * Builds a `BY` list
     *
     * @param string $type
     *
     * @return string
     */
    protected function buildBy(string $type): string
    {
        if (empty($this->store[$type])) {
            return "";
        }

        return " " . $type . " BY"
            . $this->indent($this->store[$type], ",");
    }

    /**
     * Builds the conditional string
     *
     * @param string $type
     *
     * @return string
     */
    protected function buildCondition(string $type): string
    {
        if (empty($this->store[$type])) {
            return "";
        }

        return " " . $type
            . $this->indent($this->store[$type]);
    }

    /**
     * Builds the `LIMIT` clause
     *
     * @return string
     */
    protected function buildLimit(): string
    {
        $suffix = $this->connection->getDriverName();

        if ("sqlsrv" !== $suffix) {
            $suffix = "common";
        }

        $method = "buildLimit" . ucfirst($suffix);

        return $this->$method();
    }

    /**
     * Builds the `LIMIT` clause for all drivers
     *
     * @return string
     */
    protected function buildLimitCommon(): string
    {
        $limit = "";

        if (0 !== $this->store["LIMIT"]) {
            $limit .= "LIMIT " . $this->store["LIMIT"];
        }

        if (0 !== $this->store["OFFSET"]) {
            $limit .= " OFFSET " . $this->store["OFFSET"];
        }

        if ("" !== $limit) {
            $limit = " " . ltrim($limit);
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
        $limit = "";

        if (
            "sqlsrv" === $this->connection->getDriverName() &&
            $this->store["LIMIT"] > 0 &&
            0 === $this->store["OFFSET"]
        ) {
            $limit = " TOP " . $this->store["LIMIT"];
        }

        return $limit;
    }

    /**
     * Builds the `LIMIT` clause for MSSQLServer
     *
     * @return string
     */
    protected function buildLimitSqlsrv(): string
    {
        $limit = "";

        if ($this->store["LIMIT"] > 0 && $this->store["OFFSET"] > 0) {
            $limit = " OFFSET " . $this->store["OFFSET"] . " ROWS"
                . " FETCH NEXT " . $this->store["LIMIT"] . " ROWS ONLY";
        }

        return $limit;
    }

    /**
     * Processes a value (array or string) and merges it with the store
     *
     * @param string       $store
     * @param array|string $data
     */
    protected function processValue(string $store, array | string $data): void
    {
        if (is_string($data)) {
            $data = [$data];
        }

        $this->store[$store] = array_merge(
            $this->store[$store],
            $data
        );
    }
}
