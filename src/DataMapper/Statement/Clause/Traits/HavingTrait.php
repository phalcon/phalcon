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

/**
 * @property Connection $connection
 * @property array      $store
 *
 * @method void addCondition(string $store, string $andor, string $condition, mixed $value = null, int $type = -1)
 * @method void appendCondition(string $store, string $condition, mixed $value = null, int $type = -1)
 */
trait HavingTrait
{
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
        $this->appendCondition("HAVING", $condition, $value, $type);

        return $this;
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
        $this->addCondition("HAVING", "AND ", $condition, $value, $type);

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
    ): self {
        $this->addCondition("HAVING", "OR ", $condition, $value, $type);

        return $this;
    }

    /**
     * Resets the having
     */
    public function resetHaving(): static
    {
        $this->store["HAVING"] = [];

        return $this;
    }
}
