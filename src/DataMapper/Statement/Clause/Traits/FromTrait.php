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
use Phalcon\DataMapper\Statement\Bind;
use Phalcon\DataMapper\Statement\Select;

use function array_shift;
use function ltrim;
use function strtoupper;
use function substr;
use function trim;

/**
 * @property Connection $connection
 * @property Bind       $bind
 * @property array      $store
 */
trait FromTrait
{
    /**
     * Adds table(s) in the query
     *
     * @param string $table
     *
     * @return static
     */
    public function from(string $table): static
    {
        $this->store["FROM"][] = [$table];

        return $this;
    }

    /**
     * Sets a 'JOIN' condition
     *
     * @param string     $join
     * @param string     $table
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function join(
        string $join,
        string $table,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $join = strtoupper(trim($join));
        if (!str_ends_with($join, "JOIN")) {
            $join .= " JOIN";
        }

        $condition = ltrim($condition);

        if (
            "" !== $condition
            && strtoupper(substr($condition, 0, 3)) !== "ON "
            && strtoupper(substr($condition, 0, 6)) !== "USING "
        ) {
            $condition = "ON " . $condition;
        }

        if (!empty($value)) {
            $condition .= $this->bind->inline($value, $type);
        }

        $key = array_key_last($this->store["FROM"]);

        $this->store["FROM"][$key][] = $join . " " . $table . " " . $condition;

        return $this;
    }

    /**
     * Resets the from
     */
    public function resetFrom(): static
    {
        $this->store["FROM"] = [];

        return $this;
    }

    /**
     * Start a sub-select
     *
     * @return static
     */
    public function subSelect(): Select
    {
        return new Select($this->connection, $this->bind);
    }

    /**
     * Start a `UNION`
     *
     * @return static
     */
    public function union(): static
    {
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ");

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
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ALL ");

        $this->reset();

        return $this;
    }

    /**
     * Builds the from list
     *
     * @return string
     */
    protected function buildFrom(): string
    {
        $from = [];

        if (empty($this->store["FROM"])) {
            return "";
        }

        foreach ($this->store["FROM"] as $table) {
            $from[] = array_shift($table) . $this->indent($table);
        }

        return " FROM" . $this->indent($from, ",");
    }

    /**
     * Statement builder
     *
     * @param string $suffix
     *
     * @return string
     */
    protected function getCurrentStatement(string $suffix = ""): string
    {
        $forUpdate = "";

        if ($this->forUpdate) {
            $forUpdate = " FOR UPDATE";
        }

        $statement = "SELECT"
            . $this->buildWith()
            . $this->buildFlags()
            . $this->buildLimitEarly()
            . $this->buildColumns()
            . $this->buildFrom()
            . $this->buildCondition("WHERE")
            . $this->buildBy("GROUP")
            . $this->buildCondition("HAVING")
            . $this->buildBy("ORDER")
            . $this->buildLimit()
            . $forUpdate;

        if ("" !== $this->asAlias) {
            $statement = "(" . $statement . ") AS " . $this->asAlias;
        }

        return $statement . $suffix;
    }
}
