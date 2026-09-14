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
 * @link    https://github.com/atlasphp/Atlas.Query
 * @license https://github.com/atlasphp/Atlas.Qyert/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query;

use Phalcon\Contracts\DataMapper\DataMapperTypes;
use Phalcon\DataMapper\Pdo\Exception\UnknownQueryMethod;

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
 * @phpstan-import-type datamapper_call_arguments from DataMapperTypes
 * @phpstan-import-type datamapper_clauses from DataMapperTypes
 * @phpstan-import-type datamapper_columns from DataMapperTypes
 * @phpstan-import-type datamapper_select_store from DataMapperTypes
 *
 * @property datamapper_select_store $store
 */
class Select extends AbstractConditions
{
    /**
     * @var string
     */
    public const JOIN_INNER = "INNER";

    /**
     * @var string
     */
    public const JOIN_LEFT = "LEFT";

    /**
     * @var string
     */
    public const JOIN_NATURAL = "NATURAL";

    /**
     * @var string
     */
    public const JOIN_RIGHT = "RIGHT";

    protected string $asAlias = "";

    protected bool $forUpdate = false;

    /**
     * Proxied methods to the connection
     *
     * @phpstan-param datamapper_call_arguments $params
     *
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $proxied = [
            "fetchAffected" => true,
            "fetchAll"      => true,
            "fetchAssoc"    => true,
            "fetchColumn"   => true,
            "fetchGroup"    => true,
            "fetchObject"   => true,
            "fetchObjects"  => true,
            "fetchOne"      => true,
            "fetchPairs"    => true,
            "fetchValue"    => true,
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

        throw new UnknownQueryMethod($method);
    }

    /**
     * Sets a `AND` for a `HAVING` condition
     */
    public function andHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
        $this->having($condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `HAVING` clause
     */
    public function appendHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
        $this->appendCondition("HAVING", $condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `JOIN` clause
     */
    public function appendJoin(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
        if (!empty($value)) {
            $condition .= $this->bind->bindInline($value, $type);
        }

        /** @phpstan-var int $end */
        $end = array_key_last($this->store["FROM"]);
        /** @phpstan-var int $key */
        $key = array_key_last($this->store["FROM"][$end]);

        $this->store["FROM"][$end][$key] = $this->store["FROM"][$end][$key] . $condition;

        return $this;
    }

    /**
     * The `AS` statement for the query - useful in sub-queries
     */
    public function asAlias(string $asAlias): Select
    {
        $this->asAlias = $asAlias;

        return $this;
    }

    /**
     * The columns to select from. If a key is set in the array element, the
     * key will be used as the alias
     *
     * @phpstan-param datamapper_columns $columns
     */
    public function columns(array $columns): Select
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

    public function distinct(bool $enable = true): Select
    {
        $this->setFlag("DISTINCT", $enable);

        return $this;
    }

    /**
     * Enable the `FOR UPDATE` for the query
     */
    public function forUpdate(bool $enable = true): Select
    {
        $this->forUpdate = $enable;

        return $this;
    }

    /**
     * Adds table(s) in the query
     */
    public function from(string $table): Select
    {
        $this->store["FROM"][] = [$table];

        return $this;
    }

    /**
     * Returns the compiled SQL statement
     */
    public function getStatement(): string
    {
        return implode("", $this->store["UNION"]) . $this->getCurrentStatement();
    }

    /**
     * Sets the `GROUP BY`
     *
     * @phpstan-param datamapper_clauses|string $groupBy
     */
    public function groupBy(mixed $groupBy): Select
    {
        $this->processValue("GROUP", $groupBy);

        return $this;
    }

    /**
     * Whether the query has columns or not
     */
    public function hasColumns(): bool
    {
        return !empty($this->store["COLUMNS"]);
    }

    /**
     * Sets a `HAVING` condition
     */
    public function having(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
        $this->addCondition("HAVING", "AND ", $condition, $value, $type);

        return $this;
    }

    /**
     * Sets a 'JOIN' condition
     */
    public function join(
        string $join,
        string $table,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
        $join = strtoupper(trim($join));
        if (substr($join, -4) !== "JOIN") {
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

        /** @phpstan-var int $key */
        $key = array_key_last($this->store["FROM"]);

        $this->store["FROM"][$key][] = $join . " " . $table . " " . $condition;

        return $this;
    }

    /**
     * Sets a `OR` for a `HAVING` condition
     */
    public function orHaving(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): Select {
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
     */
    public function subSelect(): Select
    {
        return new Select($this->connection, $this->bind);
    }

    /**
     * Start a `UNION`
     */
    public function union(): Select
    {
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ");

        $this->reset();

        return $this;
    }

    /**
     * Start a `UNION ALL`
     */
    public function unionAll(): Select
    {
        $this->store["UNION"][] = $this->getCurrentStatement(" UNION ALL ");

        $this->reset();

        return $this;
    }

    /**
     * Statement builder
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
