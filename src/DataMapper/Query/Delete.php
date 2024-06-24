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

use Phalcon\DataMapper\Pdo\Connection;

use function array_merge;

/**
 * Delete Query
 */
class Delete extends AbstractConditions
{
    /**
     * Delete constructor.
     *
     * @param Connection $connection
     * @param Bind       $bind
     */
    public function __construct(Connection $connection, Bind $bind)
    {
        parent::__construct($connection, $bind);

        $this->store["FROM"]      = "";
        $this->store["RETURNING"] = [];
    }

    /**
     * Adds table(s) in the query
     *
     * @param string $table
     *
     * @return Delete
     */
    public function from(string $table): self
    {
        $this->store["FROM"] = $table;

        return $this;
    }

    /**
     * Adds the `RETURNING` clause
     *
     * @param array $columns
     *
     * @return Delete
     */
    public function returning(array $columns): self
    {
        $this->store["RETURNING"] = array_merge(
            $this->store["RETURNING"],
            $columns
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return "DELETE"
            . $this->buildFlags()
            . " FROM " . $this->store["FROM"]
            . $this->buildCondition("WHERE")
            . $this->buildReturning();
    }

    /**
     * Resets the internal store
     */
    public function reset(): void
    {
        parent::reset();

        $this->store["FROM"]      = "";
        $this->store["RETURNING"] = [];
    }
}
