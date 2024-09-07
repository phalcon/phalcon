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
use Phalcon\DataMapper\Statement\AbstractStatement;
use Phalcon\DataMapper\Statement\Bind;

/**
 * @property Connection $connection
 * @property Bind       $bind
 * @property array      $store
 *
 * @method string indent(array $collection, string $glue = "")
 */
trait WithTrait
{
    protected array $ctes = [];

    protected bool $recursive = false;

    /**
     * Resets the where
     */
    public function resetWith(): static
    {
        $this->store["WITH"] = [];

        return $this;
    }

    /**
     * @param string $name
     * @param array  $columns
     * @param mixed  $statement
     *
     * @return static
     */
    public function setCte(string $name, array $columns, mixed $statement): static
    {
        $this->store['WITH'][$name] = [$columns, $statement];

        return $this;
    }

    /**
     * @param bool $recursive
     *
     * @return static
     */
    public function setRecursive(bool $recursive): static
    {
        $this->recursive = $recursive;

        return $this;
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
        $sql = $this->connection->quoteIdentifier($name);

        foreach ($columns as $key => $column) {
            $columns[$key] = $this->connection->quoteIdentifier($column);
        }

        if (!empty($columns)) {
            $sql .= ' (' . $this->indent($columns, ', ') . ')';
        }

        if ($statement instanceof AbstractStatement) {
            $this->bind->merge($statement->getBindValueObjects());
            $statement = $statement->getStatement();
        }

        $sql .= ' AS (' . $statement . ')';

        return $sql;
    }

    /**
     * Builds the with statement(s)
     *
     * @return string
     */
    protected function buildWith()
    {
        if (empty($this->ctes)) {
            return '';
        }

        $ctes = [];

        foreach ($this->ctes as $name => $info) {
            [$columns, $statement] = $info;
            $ctes[] = $this->buildCte($name, $columns, $statement);
        }

        return ($this->recursive ? 'WITH RECURSIVE' : 'WITH')
            . $this->indent($ctes, ',')
            . PHP_EOL;
    }
}
