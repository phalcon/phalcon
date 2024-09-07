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

namespace Phalcon\DataMapper\Statement\Clause\Traits;

use Phalcon\DataMapper\Pdo\Connection;

use function ltrim;
use function ucfirst;

/**
 * @property Connection $connection
 * @property array      $store
 */
trait LimitTrait
{
    /**
     * Return the existing `LIMIT`
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->store["LIMIT"];
    }

    /**
     * Return the existing `OFFSET`
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->store['OFFSET'];
    }

    /**
     * Return the current page
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->store['PAGE'];
    }

    public function getPerPage(): int
    {
        return $this->store['PER_PAGE'];
    }

    /**
     * Sets the `LIMIT` clause
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->store['LIMIT'] = $limit;

        if ($this->store['PAGE']) {
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

        if ($this->store['PAGE']) {
            $this->store['PAGE']  = 0;
            $this->store['LIMIT'] = 0;
        }

        return $this;
    }

    /**
     * Set the current page
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
     * Set the perPage
     *
     * @param int $perPage
     *
     * @return static
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
     */
    public function resetLimit(): static
    {
        $this->store["LIMIT"]  = 0;
        $this->store["OFFSET"] = 0;

        return $this;
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
     * Calculate Paging, Limit, Offset
     *
     * @return void
     */
    protected function setPagingLimitOffset(): void
    {
        $this->store["LIMIT"]  = 0;
        $this->store["OFFSET"] = 0;

        if ($this->store["PAGE"]) {
            $this->store["LIMIT"]  = $this->store["PER_PAGE"];
            $this->store["OFFSET"] = $this->store["PER_PAGE"] * ($this->store["PAGE"] - 1);
        }
    }
}
