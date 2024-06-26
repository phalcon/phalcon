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

use BadMethodCallException;
use PDO;

use function array_key_last;
use function array_merge;
use function array_shift;
use function call_user_func_array;
use function implode;
use function is_int;
use function ltrim;
use function strtoupper;
use function substr;
use function trim;

/**
 * Select Query
 *
 * @method int    fetchAffected(string $statement, array $values = [])
 * @method array  fetchAll(string $statement, array $values = [])
 * @method array  fetchAssoc(string $statement, array $values = [])
 * @method array  fetchColumn(string $statement, array $values = [], int $column = 0)
 * @method array  fetchGroup(string $statement, array $values = [], int $flags = PDO::FETCH_ASSOC)
 * @method object fetchObject(string $statement, array $values = [], string $className = "stdClass", array $arguments = [])
 * @method array  fetchObjects(string $statement, array $values = [], string $className = "stdClass", array $arguments = [])
 * @method array  fetchOne(string $statement, array $values = [])
 * @method array  fetchPairs(string $statement, array $values = [])
 * @method array  fetchUnique(string $statement, array $values = [])
 * @method mixed  fetchValue(string $statement, array $values = [])
 */
class Select extends AbstractConditions
{
    public const JOIN_INNER   = "INNER";
    public const JOIN_LEFT    = "LEFT";
    public const JOIN_NATURAL = "NATURAL";
    public const JOIN_RIGHT   = "RIGHT";

    /**
     * @var string
     */
    protected string $asAlias = "";

    /**
     * @var bool
     */
    protected bool $forUpdate = false;

    /**
     * Proxied methods to the connection
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $proxied = [
            'fetchAffected' => true,
            'fetchAll'      => true,
            'fetchAssoc'    => true,
            'fetchColumn'   => true,
            'fetchGroup'    => true,
            'fetchObject'   => true,
            'fetchObjects'  => true,
            'fetchOne'      => true,
            'fetchPairs'    => true,
            'fetchUnique'   => true,
            'fetchValue'    => true,
        ];

        if (isset($proxied[$method])) {
            return call_user_func_array(
                [
                    $this->connection,
                    $method,
                ],
                array_merge(
                    [
                        $this->getStatement(),
                        $this->getBindValues(),
                    ],
                    $params
                )
            );
        }

        throw new BadMethodCallException(
            "Unknown method: [" . $method . "]"
        );
    }

    /**
     * Sets a `AND` for a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return Select
     */
    public function andHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
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
     * @return Select
     */
    public function appendHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
        $this->appendCondition("HAVING", $condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `JOIN` clause
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return Select
     */
    public function appendJoin(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
        if (!empty($value)) {
            $condition .= $this->bind->bindInline($value, $type);
        }

        $end = array_key_last($this->store["FROM"]);
        $key = array_key_last($this->store["FROM"][$end]);

        $this->store["FROM"][$end][$key] .= $condition;

        return $this;
    }

    /**
     * The `AS` statement for the query - useful in sub-queries
     *
     * @param string $asAlias
     *
     * @return Select
     */
    public function asAlias(string $asAlias): self
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
     * @return Select
     */
    public function columns(array $columns): self
    {
        $localColumns = [];
        foreach ($columns as $key => $value) {
            if (is_int($key)) {
                $localColumns[] = $value;
            } else {
                $localColumns[] = $value . " AS " . $key;
            }
        }

        $this->store["COLUMNS"] = array_merge(
            $this->store["COLUMNS"],
            $localColumns
        );

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return Select
     */
    public function distinct(bool $enable = true): self
    {
        $this->setFlag("DISTINCT", $enable);

        return $this;
    }

    /**
     * Enable the `FOR UPDATE` for the query
     *
     * @param bool $enable
     *
     * @return Select
     */
    public function forUpdate(bool $enable = true): self
    {
        $this->forUpdate = $enable;

        return $this;
    }

    /**
     * Adds table(s) in the query
     *
     * @param string $table
     *
     * @return Select
     */
    public function from(string $table): self
    {
        $this->store["FROM"][] = [$table];

        return $this;
    }

    /**
     * Returns the compiled SQL statement
     *
     * @return string
     */
    public function getStatement(): string
    {
        return implode("", $this->store["UNION"]) . $this->getCurrentStatement();
    }

    /**
     * Sets the `GROUP BY`
     *
     * @param array|string $groupBy
     *
     * @return Select
     */
    public function groupBy(array | string $groupBy): self
    {
        $this->processValue("GROUP", $groupBy);

        return $this;
    }

    /**
     * Whether the query has columns or not
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return count($this->store["COLUMNS"]) > 0;
    }

    /**
     * Sets a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return Select
     */
    public function having(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
        $this->addCondition("HAVING", "AND ", $condition, $value, $type);

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
     * @return Select
     */
    public function join(
        string $join,
        string $table,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
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
            $condition .= $this->bind->bindInline($value, $type);
        }

        $key = array_key_last($this->store["FROM"]);

        $this->store["FROM"][$key][] = $join . " " . $table . " " . $condition;

        return $this;
    }

    /**
     * Sets a `OR` for a `HAVING` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return Select
     */
    public function orHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): self {
        $this->addCondition("HAVING", "OR ", $condition, $value, $type);

        return $this;
    }

    /**
     * Resets the internal collections
     */
    public function reset(): void
    {
        parent::reset();

        $this->asAlias   = "";
        $this->forUpdate = false;
    }

    /**
     * Start a sub-select
     *
     * @return Select
     */
    public function subSelect(): self
    {
        return new Select($this->connection, $this->bind);
    }

    /**
     * Start a `UNION`
     *
     * @return Select
     */
    public function union(): self
    {
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ");

        $this->reset();

        return $this;
    }

    /**
     * Start a `UNION ALL`
     *
     * @return Select
     */
    public function unionAll(): self
    {
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ALL ");

        $this->reset();

        return $this;
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

    /**
     * Builds the columns list
     *
     * @return string
     */
    private function buildColumns(): string
    {
        if (!$this->hasColumns()) {
            $columns = ["*"];
        } else {
            $columns = $this->store["COLUMNS"];
        }

        return $this->indent($columns, ",");
    }

    /**
     * Builds the from list
     *
     * @return string
     */
    private function buildFrom(): string
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
}
