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
use function get_object_vars;
use function implode;
use function is_object;
use function is_string;
use function rtrim;

abstract class AbstractStatement
{
    /**
     * @var Bind
     */
    protected Bind $bind;

    /**
     * Internal store
     *
     * @var array
     */
    protected array $store = [];

    /**
     * Constructor
     *
     * @param string $driver
     */
    public function __construct(
        protected string $driver
    ) {
        $this->bind           = new Bind();
        $this->store['UNION'] = [];

        $this->reset();
    }

    public function __clone()
    {
        $vars = get_object_vars($this);

        foreach ($vars as $name => $prop) {
            if (is_object($prop)) {
                $this->$name = clone $prop;
            }
        }
    }

    /**
     * Binds a value inline
     *
     * @param mixed $value
     * @param int   $type
     *
     * @return string
     */
    public function bindInline(mixed $value, int $type = -1): string
    {
        return $this->bind->inline($value, $type);
    }

    /**
     * Binds a value - auto-detects the type if necessary
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $type
     *
     * @return static
     */
    public function bindValue(
        string $key,
        mixed $value,
        int $type = -1
    ): static {
        $this->bind->setValue($key, $value, $type);

        return $this;
    }

    /**
     * Binds an array of values
     *
     * @param array $values
     *
     * @return static
     */
    public function bindValues(array $values): static
    {
        $this->bind->setValues($values);

        return $this;
    }

    /**
     * Builds the with statement(s)
     *
     * @return string
     */
    public function buildWith(): string
    {
        if (empty($this->store['WITH']['ctes'])) {
            return '';
        }

        $recursive = ($this->store['WITH']['recursive'] ? 'WITH RECURSIVE' : 'WITH');

        /**
         * Once again, this is a bit more difficult to read but it is the fastest
         * implementation.
         *
         * We are traversing the $ctes array and extracting the key and the
         * value. The value is then split into the first element which is the
         * columns and the second which is the statement. With those, we can
         * then call the `buildCte` method to get the resulting string.
         *
         * This reduces the need to create a separate array and populate it
         * with the results from `buildCte`
         */
        $ctes = array_map(
            fn($name, $info) => $this->buildCte($name, $info[0], $info[1]),
            array_keys($this->store['WITH']['ctes']),
            $this->store['WITH']['ctes']
        );

        $output = $recursive . $this->indent($ctes, ',');

        return rtrim($output, ' ') . ' ';
    }

    /**
     * Returns all the bound values
     *
     * @return array
     */
    public function getBindValues(): array
    {
        return $this->bind->toArray();
    }

    /**
     * Return the generated statement
     *
     * @return string
     */
    abstract public function getStatement(): string;

    /**
     * Instantiate a new object
     *
     * @param string $driver
     *
     * @return static
     */
    public static function new(string $driver): static
    {
        return new static($driver);
    }

    /**
     * @param string $driver
     * @param string $name
     *
     * @return string
     */
    public function quote(string $driver, string $name): string
    {
        $map = match ($driver) {
            'mysql'  => ['`', '`'],
            'sqlsrv' => ['[', ']'],
            default  => ['"', '"']
        };

        return $map[0] . $name . $map[1];
    }

    /**
     * Quotes an identifier/field
     *
     * @param string $name
     *
     * @return string
     */
    public function quoteIdentifier(string $name): string
    {
        return $this->quote($this->driver, $name);
    }

    /**
     * Reset the internal store
     *
     * @return void
     */
    public function reset(): void
    {
        $this->store['COLUMNS']  = [];
        $this->store['FLAGS']    = [];
        $this->store['FROM']     = [];
        $this->store['GROUP']    = [];
        $this->store['HAVING']   = [];
        $this->store['LIMIT']    = 0;
        $this->store['ORDER']    = [];
        $this->store['OFFSET']   = 0;
        $this->store['PAGE']     = 0;
        $this->store['PER_PAGE'] = 10;
        $this->store['WHERE']    = [];
        $this->store['WITH']     = [
            'ctes'      => [],
            'recursive' => false,
        ];
    }

    /**
     * Rests the WITH clause
     *
     * @return void
     */
    public function resetWith(): void
    {
        $this->store['WITH'] = [
            'ctes'      => [],
            'recursive' => false,
        ];
    }

    /**
     * Sets a flag for the query such as 'DISTINCT'
     *
     * @param string $flag
     * @param bool   $enable
     *
     * @return $this
     */
    public function setFlag(string $flag, bool $enable = true): static
    {
        if (true === $enable) {
            $this->store['FLAGS'][$flag] = true;
        } else {
            unset($this->store['FLAGS'][$flag]);
        }

        return $this;
    }

    /**
     * Add a CTE to the WITH clause
     *
     * @param string                   $name
     * @param string|AbstractStatement $statement
     *
     * @return $this
     */
    public function with(
        string $name,
        string | AbstractStatement $statement
    ): static {
        $this->store['WITH']['ctes'][$name] = [[], $statement];

        return $this;
    }

    /**
     * Add WITH columns
     *
     * @param string                   $name
     * @param array                    $columns
     * @param string|AbstractStatement $statement
     *
     * @return $this
     */
    public function withColumns(
        string $name,
        array $columns,
        string | AbstractStatement $statement
    ): static {
        $this->store['WITH']['ctes'][$name] = [$columns, $statement];

        return $this;
    }

    /**
     * Sets the recursive flag for WITH
     *
     * @param bool $recursive
     *
     * @return $this
     */
    public function withRecursive(bool $recursive = true): static
    {
        $this->store['WITH']['recursive'] = $recursive;

        return $this;
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
            return '';
        }

        return ' ' . $type . ' BY'
            . $this->indent($this->store[$type], ',');
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
            return '';
        }

        return ' ' . $type
            . $this->indent($this->store[$type]);
    }

    /**
     * @param string                   $name
     * @param array                    $columns
     * @param string|AbstractStatement $statement
     *
     * @return string
     */
    protected function buildCte(
        string $name,
        array $columns,
        string | AbstractStatement $statement
    ): string {
        $sql = $this->quote($this->driver, $name);

        /**
         * Using `array_map` instead of a loop to get the quoted
         * columns. A bit harder to read but faster
         */
        $columns = array_map(
            fn($column) => $this->quote($this->driver, $column),
            $columns
        );

        if (!empty($columns)) {
            $sql .= ' (' . implode(', ', $columns) . ')';
        }

        if ($statement instanceof AbstractStatement) {
            $this->bind->merge($statement->getBindValues());
            $statement = $statement->getStatement();
        }

        $sql .= ' AS (' . $statement . ')';

        return $sql;
    }

    /**
     * Builds the flags statement(s)
     *
     * @return string
     */
    protected function buildFlags()
    {
        if (empty($this->store['FLAGS'])) {
            return '';
        }

        return ' ' . implode(' ', array_keys($this->store['FLAGS']));
    }

    /**
     * Builds the `RETURNING` clause
     *
     * @return string
     */
    protected function buildReturning(): string
    {
        if (empty($this->store['RETURNING'])) {
            return '';
        }

        return ' RETURNING' . $this->indent($this->store['RETURNING'], ',');
    }

    /**
     * Indents a collection
     *
     * @param array  $collection
     * @param string $glue
     *
     * @return string
     */
    protected function indent(array $collection, string $glue = ''): string
    {
        if (empty($collection)) {
            return '';
        }

        return ' ' . implode($glue . ' ', $collection);
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

        $this->store[$store] = array_merge($this->store[$store], $data);
    }
}
